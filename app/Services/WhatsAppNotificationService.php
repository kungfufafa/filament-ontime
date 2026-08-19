<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppNotificationService
{
    /**
     * Normalize phone number to international 628... format.
     */
    public static function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $phone);
        if (empty($digits)) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * Core method to send generic WhatsApp message via WAG Gateway.
     *
     * @return array{success: bool, api_sent: bool, api_error: string|null, message: string, phone: string|null, wa_url: string|null}
     */
    public static function sendMessage(?string $phone, string $message, string $purpose = 'notification', ?int $expiresInMinutes = null): array
    {
        $normalizedPhone = static::normalizePhoneNumber($phone);
        $waUrl = $normalizedPhone ? 'https://wa.me/'.$normalizedPhone.'?text='.rawurlencode($message) : null;

        Log::info("WhatsApp Message [{$purpose}] for ({$normalizedPhone}):\n{$message}");

        $wagConfig = config('services.wag', []);
        $wagUrl = $wagConfig['url'] ?? env('WAG_URL');
        $wagApiKey = $wagConfig['api_key'] ?? env('WAG_API_KEY');

        $apiSuccess = false;
        $apiError = null;

        if ($wagUrl && $wagApiKey && $normalizedPhone) {
            try {
                $idempotencyKey = (string) Str::uuid();

                $payload = [
                    'idempotency_key' => $idempotencyKey,
                    'idempotencyKey' => $idempotencyKey,
                    'recipient' => [
                        'type' => 'phone',
                        'value' => $normalizedPhone,
                    ],
                    'message' => [
                        'type' => 'text',
                        'text' => $message,
                    ],
                    'purpose' => $purpose,
                    'mode' => 'sync',
                ];

                if ($expiresInMinutes !== null && $expiresInMinutes > 0) {
                    $payload['expires_at'] = now()->addMinutes($expiresInMinutes)->toIso8601String();
                }

                // Synchronous HTTP request to WAG Gateway
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$wagApiKey,
                    'x-api-key' => $wagApiKey,
                    'Accept' => 'application/json',
                    'X-Idempotency-Key' => $idempotencyKey,
                    'Idempotency-Key' => $idempotencyKey,
                ])->timeout(15)->post($wagUrl, $payload);

                if ($response->successful()) {
                    $apiSuccess = true;
                    Log::info("WhatsApp Gateway (WAG) message [{$purpose}] sent successfully to {$normalizedPhone}");
                } else {
                    $resJson = $response->json();
                    $errorMessage = $resJson['message'] ?? $response->body();
                    $apiError = "WAG API ({$response->status()}): {$errorMessage}";
                    Log::warning("WhatsApp Gateway (WAG) API error [{$purpose}]: {$apiError}");
                }
            } catch (\Throwable $e) {
                $apiError = $e->getMessage();
                Log::warning("WhatsApp Gateway (WAG) API failed [{$purpose}]: ".$apiError);
            }
        }

        return [
            'success' => true,
            'api_sent' => $apiSuccess,
            'api_error' => $apiError,
            'message' => $message,
            'phone' => $normalizedPhone,
            'wa_url' => $waUrl,
        ];
    }

    /**
     * Format and send WhatsApp notification for user account credentials.
     * Uses purpose = 'notification'.
     *
     * @return array{success: bool, api_sent: bool, api_error: string|null, message: string, phone: string|null, wa_url: string|null}
     */
    public static function sendAccountCredentials(string $name, ?string $phone, string $email, string $password, string $role): array
    {
        $normalizedPhone = static::normalizePhoneNumber($phone);

        $message = "Halo {$name},\n\n"
            ."Akun Anda untuk sistem OnTime telah berhasil dibuat!\n\n"
            ."Detail Kredensial Login Anda:\n"
            ."- Email: {$email}\n"
            .($normalizedPhone ? "- No. HP: {$normalizedPhone}\n" : '')
            ."- Password: {$password}\n"
            ."- Role Akses: {$role}\n\n"
            ."Anda dapat melakukan login ke sistem melalui 2 cara:\n"
            ."1. Login Email & Password:\n"
            ."   - Masukkan Email: {$email}\n"
            ."   - Masukkan Password: {$password}\n\n"
            ."2. Login via WhatsApp OTP:\n"
            ."   - Buka halaman login sistem\n"
            ."   - Pilih opsi 'Login via WhatsApp'\n"
            .'   - Masukkan Nomor WhatsApp Anda: '.($normalizedPhone ?: ($phone ?: '-'))."\n"
            ."   - Masukkan kode OTP yang dikirimkan ke WhatsApp ini untuk verifikasi dan masuk.\n\n"
            ."Mohon segera login dan jaga kerahasiaan kredensial Anda.\n\n"
            .'Terima kasih.';

        return static::sendMessage($phone, $message, 'notification');
    }

    /**
     * Format and send WhatsApp OTP verification code.
     * Uses purpose = 'otp'.
     *
     * @return array{success: bool, api_sent: bool, api_error: string|null, message: string, phone: string|null, wa_url: string|null}
     */
    public static function sendOtpCode(?string $phone, string $otpCode, int $expiresInMinutes = 5): array
    {
        $message = "Kode OTP verifikasi login OnTime Anda adalah: {$otpCode}\n\n"
            ."Kode ini berlaku selama {$expiresInMinutes} menit. Jangan bagikan kode OTP ini kepada siapapun demi keamanan akun Anda.";

        return static::sendMessage($phone, $message, 'otp', $expiresInMinutes);
    }
}
