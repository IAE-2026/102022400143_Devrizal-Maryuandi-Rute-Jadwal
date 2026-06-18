<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MessagePublisherService
{
    private string $publishUrl;
    private string $teamId;
    private ?string $m2mApiKey;
    private ?string $m2mToken;

    public function __construct()
    {
        $this->publishUrl = env('MESSAGE_PUBLISH_URL', 'https://iae-sso.virtualfri.id/api/v1/messages/publish');
        $this->teamId     = env('SOAP_TEAM_ID', 'TEAM-12');
        $this->m2mApiKey  = env('M2M_API_KEY');
        $this->m2mToken   = env('M2M_TOKEN');
    }

    /**
     * Fetch token M2M segar dari SSO dosen.
     * Token di .env sering expired (1 jam), jadi fetch langsung lebih reliable.
     */
    private function getFreshM2mToken(): ?string
    {
        try {
            $tokenUrl = env('SSO_BASE_URL', 'https://iae-sso.virtualfri.id') . '/api/v1/auth/token';

            $response = Http::timeout(10)->post($tokenUrl, [
                'api_key' => $this->m2mApiKey,
            ]);

            if ($response->successful()) {
                $freshToken = $response->json('token');
                Log::info('[MessagePublisher] Fresh M2M token obtained');
                return $freshToken;
            }

            Log::warning('[MessagePublisher] Gagal fetch fresh token', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[MessagePublisher] Exception saat fetch token', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Publish event route.seat_reserved ke message API dosen.
     * Jika gagal, hanya di-log — tidak throw exception agar tidak crash response utama.
     *
     * @param  string  $bearerToken JWT token
     * @param  int     $routeId
     * @param  string  $bookingReference
     * @param  int     $reservedSeats
     * @param  int     $availableSeats
     * @param  string  $receiptNumber
     */
    public function publishSeatReserved(
        string $bearerToken,
        int    $routeId,
        string $bookingReference,
        int    $reservedSeats,
        int    $availableSeats,
        string $receiptNumber
    ): void {
        $event = [
            'event_id'     => Str::uuid()->toString(),
            'event_type'   => 'route.seat_reserved',
            'occurred_at'  => now()->toIso8601String(),
            'service_name' => env('SERVICE_NAME', 'Route-Schedule-Service'),
            'data'         => [
                'route_id'             => $routeId,
                'booking_reference'    => $bookingReference,
                'reserved_seats'       => $reservedSeats,
                'available_seats'      => $availableSeats,
                'audit_receipt_number' => $receiptNumber,
            ],
        ];

        // Central message API dosen mewajibkan field "message" (object atau string).
        // Event dikirim sebagai object di dalam "message".
        // team_id + routing_key ditambahkan supaya pesan ter-tag ke board TEAM-12
        // (mengikuti pola TeamID di SOAP audit). Sesuaikan jika dosen punya spec lain.
        $payload = [
            'team_id'     => $this->teamId,
            'routing_key' => $event['event_type'],
            'message'     => $event,
        ];

        try {
            Log::info('[MessagePublisher] Mengirim event', [
                'event_type' => $event['event_type'],
                'route_id'   => $routeId,
            ]);

            // Publish ke broker pusat = aksi machine-to-machine.
            // Fetch token segar dari SSO agar tidak pakai token expired di .env.
            // Fallback: M2M_TOKEN dari .env → bearer token warga.
            $authToken = $this->getFreshM2mToken()
                ?? (!empty($this->m2mToken) ? $this->m2mToken : $bearerToken);

            $headers = [
                'Authorization' => 'Bearer ' . $authToken,
                'Content-Type'  => 'application/json',
            ];
            // Header M2M key (jika diisi di .env) untuk autentikasi machine-to-machine
            if (!empty($this->m2mApiKey)) {
                $headers['X-API-KEY'] = $this->m2mApiKey;
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($this->publishUrl, $payload);

            if ($response->successful()) {
                Log::info('[MessagePublisher] Event berhasil dipublish', [
                    'event_id'   => $event['event_id'],
                    'event_type' => $event['event_type'],
                    'status'     => $response->status(),
                    'response'   => $response->body(),
                ]);
            } else {
                Log::warning('[MessagePublisher] Event gagal dipublish', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            // Tidak throw — error publish tidak boleh crash response utama
            Log::error('[MessagePublisher] Exception saat publish event', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }

    /**
     * Publish event route.seat_released ke message API dosen.
     * Dipakai saat pembatalan booking / gagal bayar (kompensasi reserve-seats).
     * Sama seperti publishSeatReserved: gagal hanya di-log, tidak throw.
     *
     * @param  string  $bearerToken JWT token
     * @param  int     $routeId
     * @param  string  $bookingReference
     * @param  int     $releasedSeats     Jumlah kursi yang dikembalikan
     * @param  int     $availableSeats    Sisa kursi setelah dikembalikan
     * @param  string  $receiptNumber     ReceiptNumber audit RELEASE_SEATS
     */
    public function publishSeatReleased(
        string $bearerToken,
        int    $routeId,
        string $bookingReference,
        int    $releasedSeats,
        int    $availableSeats,
        string $receiptNumber
    ): void {
        $event = [
            'event_id'     => Str::uuid()->toString(),
            'event_type'   => 'route.seat_released',
            'occurred_at'  => now()->toIso8601String(),
            'service_name' => env('SERVICE_NAME', 'Route-Schedule-Service'),
            'data'         => [
                'route_id'             => $routeId,
                'booking_reference'    => $bookingReference,
                'released_seats'       => $releasedSeats,
                'available_seats'      => $availableSeats,
                'audit_receipt_number' => $receiptNumber,
            ],
        ];

        $payload = [
            'team_id'     => $this->teamId,
            'routing_key' => $event['event_type'],
            'message'     => $event,
        ];

        try {
            Log::info('[MessagePublisher] Mengirim event', [
                'event_type' => $event['event_type'],
                'route_id'   => $routeId,
            ]);

            $authToken = $this->getFreshM2mToken()
                ?? (!empty($this->m2mToken) ? $this->m2mToken : $bearerToken);

            $headers = [
                'Authorization' => 'Bearer ' . $authToken,
                'Content-Type'  => 'application/json',
            ];
            if (!empty($this->m2mApiKey)) {
                $headers['X-API-KEY'] = $this->m2mApiKey;
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($this->publishUrl, $payload);

            if ($response->successful()) {
                Log::info('[MessagePublisher] Event berhasil dipublish', [
                    'event_id'   => $event['event_id'],
                    'event_type' => $event['event_type'],
                    'status'     => $response->status(),
                    'response'   => $response->body(),
                ]);
            } else {
                Log::warning('[MessagePublisher] Event gagal dipublish', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[MessagePublisher] Exception saat publish event', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }
}
