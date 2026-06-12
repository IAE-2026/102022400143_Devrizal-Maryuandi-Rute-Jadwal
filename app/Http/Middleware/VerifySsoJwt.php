<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifySsoJwt
{
    /**
     * Handle an incoming request.
     * 1. Ambil Bearer token dari header Authorization
     * 2. Fetch public key dari JWKS endpoint dosen (di-cache 1 jam)
     * 3. Verifikasi signature JWT (RS256)
     * 4. Baca payload → ambil identitas dan role
     * 5. Mapping cloud role → local role
     * 6. Simpan/update user di database lokal
     * 7. Attach user + raw token ke request
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ambil token dari header
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized('Authorization header tidak ada atau bukan Bearer token');
        }

        $token = substr($authHeader, 7);

        try {
            // 2. Fetch JWKS dan decode JWT
            $keySet  = $this->getJwks();
            $decoded = JWT::decode($token, $keySet);
            $payload = (array) $decoded;

            Log::debug('[JWT] Token berhasil di-decode', [
                'sub'   => $payload['sub'] ?? null,
                'email' => $payload['email'] ?? null,
            ]);

            // 3. Ambil identitas dari payload
            $sub      = $payload['sub'] ?? null;
            $email    = $payload['email'] ?? ($payload['sub'] ?? null);
            $name     = $payload['name'] ?? 'SSO User';

            // 4. Ambil cloud role dari payload
            // Coba beberapa kemungkinan nama field role
            $cloudRole = $payload['role']
                ?? $payload['roles']
                ?? $payload['user_role']
                ?? $payload['scope']
                ?? 'user';

            // Jika roles adalah array, ambil yang pertama
            if (is_array($cloudRole)) {
                $cloudRole = $cloudRole[0] ?? 'user';
            }

            // M2M token (client_credentials) = panggilan antar-service.
            // Token tipe ini tidak punya claim role, jadi dipetakan ke role 'service'.
            $tokenType = $payload['token_type'] ?? null;
            $grantType = $payload['grant_type'] ?? null;
            if ($tokenType === 'm2m' || $grantType === 'client_credentials') {
                $cloudRole = 'service';
                // Identitas service diambil dari claim app
                $app   = isset($payload['app']) ? (array) $payload['app'] : [];
                $name  = $app['name'] ?? $name;
                $email = $email ?? $sub;
            }

            // 5. Mapping cloud role → local role
            $localRole = $this->mapRole($cloudRole);

            // 6. Simpan/update user di database lokal
            // Cari by sso_sub dulu, fallback ke email (untuk user yang sudah ada sebelum SSO)
            $existingUser = User::where('sso_sub', $sub)
                ->orWhere('email', $email)
                ->first();

            $isMachine = ($tokenType === 'm2m' || $grantType === 'client_credentials');

            if ($existingUser) {
                // Update sso_sub + identitas. Untuk M2M, role selalu di-refresh ke 'service'
                // (jangan pertahankan role lama yang mungkin terlanjur 'viewer').
                $existingUser->update([
                    'sso_sub'    => $sub,
                    'name'       => $name,
                    'email'      => $email,
                    'cloud_role' => $cloudRole,
                    'local_role' => $isMachine ? $localRole : ($existingUser->local_role ?? $localRole),
                ]);
                $user = $existingUser->fresh();
            } else {
                // User baru — set semua field termasuk local_role dari mapping
                $user = User::create([
                    'sso_sub'    => $sub,
                    'name'       => $name,
                    'email'      => $email,
                    'cloud_role' => $cloudRole,
                    'local_role' => $localRole,
                    'password'   => null,
                ]);
            }

            Log::info('[JWT] Role resolved', [
                'sub'        => $sub,
                'token_type' => $tokenType,
                'is_machine' => $isMachine,
                'local_role' => $user->local_role,
            ]);

            // 7. Attach ke request
            $request->attributes->set('auth_user', $user);
            $request->attributes->set('jwt_token', $token);
            $request->attributes->set('jwt_payload', $payload);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->unauthorized('Token sudah expired, silakan login ulang');
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return $this->unauthorized('Signature JWT tidak valid');
        } catch (\Throwable $e) {
            Log::error('[JWT] Gagal memverifikasi token', ['error' => $e->getMessage()]);
            return $this->unauthorized('Token tidak valid: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Fetch JWKS dari SSO dosen dan convert ke format yang bisa dipakai firebase/php-jwt.
     * Di-cache selama 60 menit supaya tidak fetch tiap request.
     */
    private function getJwks(): \Firebase\JWT\Key|array
    {
        $jwksUrl = env('SSO_JWKS_URI', 'https://iae-sso.virtualfri.id/api/v1/auth/jwks');

        $jwksData = Cache::remember('sso_jwks', 3600, function () use ($jwksUrl) {
            $response = file_get_contents($jwksUrl);
            if ($response === false) {
                throw new \Exception('Gagal fetch JWKS dari SSO: ' . $jwksUrl);
            }
            return json_decode($response, true);
        });

        return JWK::parseKeySet($jwksData);
    }

    /**
     * Mapping cloud role dari SSO → local role sistem kita.
     * Cek tabel roles dulu, fallback ke mapping hardcode.
     */
    private function mapRole(string $cloudRole): string
    {
        // Cek di tabel roles dulu
        $roleMapping = \DB::table('roles')
            ->where('cloud_role', $cloudRole)
            ->value('local_role');

        if ($roleMapping) {
            return $roleMapping;
        }

        // Fallback mapping hardcode
        return match (strtolower($cloudRole)) {
            'admin'     => 'admin',
            'operator'  => 'operator',
            'service'   => 'service',
            default     => 'viewer',
        };
    }

    /**
     * Response 401 Unauthorized dengan format standard.
     */
    private function unauthorized(string $message): Response
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => null,
        ], 401);
    }
}
