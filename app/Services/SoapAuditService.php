<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SoapAuditService
{
    private string $endpoint;
    private string $teamId;

    public function __construct()
    {
        $this->endpoint = env('SOAP_ENDPOINT', 'https://iae-sso.virtualfri.id/soap/v1/audit');
        $this->teamId   = env('SOAP_TEAM_ID', 'TEAM-12');
    }

    /**
     * Kirim audit ke SOAP server dosen.
     * Mengembalikan ReceiptNumber jika sukses.
     *
     * @param  string  $bearerToken  JWT token dari user yang sedang request
     * @param  string  $activityName Nama aktivitas bisnis (contoh: RESERVE_SEATS)
     * @param  array   $logData      Data transaksi yang akan diaudit
     * @return string  ReceiptNumber dari SOAP response
     *
     * @throws \Exception jika SOAP gagal
     */
    public function sendAudit(string $bearerToken, string $activityName, array $logData): string
    {
        $logContent = json_encode($logData, JSON_UNESCAPED_SLASHES);
        $xmlBody    = $this->buildSoapEnvelope($activityName, $logContent);

        Log::info('[SOAP] Mengirim audit request', [
            'endpoint'      => $this->endpoint,
            'activity_name' => $activityName,
            'log_data'      => $logData,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearerToken,
            'Content-Type'  => 'text/xml; charset=utf-8',
        ])
        ->withBody($xmlBody, 'text/xml')
        ->timeout(30)
        ->post($this->endpoint);

        if ($response->failed()) {
            Log::error('[SOAP] Request gagal', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
            throw new \Exception('SOAP audit request gagal: HTTP ' . $response->status());
        }

        $receiptNumber = $this->parseReceiptNumber($response->body());

        Log::info('[SOAP] Audit berhasil', [
            'receipt_number' => $receiptNumber,
        ]);

        return $receiptNumber;
    }

    /**
     * Build SOAP XML Envelope sesuai format dosen.
     */
    private function buildSoapEnvelope(string $activityName, string $logContent): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
    <soap:Body>
        <iae:AuditRequest>
            <iae:TeamID>{$this->teamId}</iae:TeamID>
            <iae:ActivityName>{$activityName}</iae:ActivityName>
            <iae:LogContent><![CDATA[{$logContent}]]></iae:LogContent>
        </iae:AuditRequest>
    </soap:Body>
</soap:Envelope>
XML;
    }

    /**
     * Parse ReceiptNumber dari XML response SOAP.
     */
    private function parseReceiptNumber(string $xmlResponse): string
    {
        Log::debug('[SOAP] Raw response', ['xml' => $xmlResponse]);

        // Cek status dulu
        if (preg_match('/<iae:Status[^>]*>(.*?)<\/iae:Status>/s', $xmlResponse, $statusMatch)) {
            $status = trim($statusMatch[1]);
            if ($status !== 'SUCCESS') {
                throw new \Exception('SOAP audit status bukan SUCCESS: ' . $status);
            }
        }

        // Ambil ReceiptNumber
        if (preg_match('/<iae:ReceiptNumber[^>]*>(.*?)<\/iae:ReceiptNumber>/s', $xmlResponse, $match)) {
            return trim($match[1]);
        }

        throw new \Exception('ReceiptNumber tidak ditemukan di response SOAP. Response: ' . $xmlResponse);
    }

    /**
     * Kembalikan raw XML body untuk disimpan ke audit_logs.
     */
    public function buildRequestPayload(string $activityName, array $logData): string
    {
        return $this->buildSoapEnvelope($activityName, json_encode($logData));
    }
}
