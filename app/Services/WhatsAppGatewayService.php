<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppGatewayService
{
    protected string $url;

    protected ?string $apiKey;

    public function __construct()
    {
        $this->url = config('services.wag.url', 'http://wag.test/send');
        $this->apiKey = config('services.wag.api_key');
    }

    /**
     * Normalize Indonesian phone number to international format (628...)
     */
    public function normalizePhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/\D/', '', $phone);

        if (str_starts_with($cleaned, '0')) {
            return '62'.substr($cleaned, 1);
        }

        if (str_starts_with($cleaned, '8')) {
            return '62'.$cleaned;
        }

        return $cleaned;
    }

    /**
     * Send WhatsApp message via WAG (WhatsApp Gateway) service
     */
    public function sendMessage(string $phone, string $message): bool
    {
        $target = $this->normalizePhoneNumber($phone);

        // 1. Try WAG API v1 endpoint if available or if domain matches
        $v1Url = $this->getV1ApiUrl($this->url);
        if ($v1Url) {
            $success = $this->sendMessageV1($v1Url, $target, $message);
            if ($success) {
                return true;
            }
        }

        // 2. Fallback to simple POST payload (legacy / simple webhook gateways)
        return $this->sendMessageLegacy($this->url, $target, $message);
    }

    protected function getV1ApiUrl(string $baseUrl): ?string
    {
        if (str_contains($baseUrl, '/api/v1/messages')) {
            return $baseUrl;
        }

        $parsed = parse_url($baseUrl);
        if (! isset($parsed['host'])) {
            return null;
        }

        // If local mock or test endpoint like wag.test, do not auto-convert to v1 unless requested
        if (str_contains($parsed['host'], 'wag.test') || str_contains($parsed['host'], 'gateway-hub.test') || str_contains($baseUrl, 'localhost')) {
            return null;
        }

        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'];
        $port = isset($parsed['port']) ? ':'.$parsed['port'] : '';

        return "{$scheme}://{$host}{$port}/api/v1/messages";
    }

    protected function sendMessageV1(string $v1Url, string $target, string $message): bool
    {
        try {
            $idempotencyKey = (string) Str::uuid();
            $payload = [
                'idempotency_key' => $idempotencyKey,
                'recipient' => [
                    'type' => 'phone',
                    'value' => $target,
                ],
                'message' => [
                    'type' => 'text',
                    'text' => $message,
                ],
                'purpose' => 'otp',
                'mode' => 'sync',
                'expires_at' => now()->addMinutes(5)->toIso8601String(),
            ];

            $headers = [
                'Accept' => 'application/json',
                'Idempotency-Key' => $idempotencyKey,
                'X-Idempotency-Key' => $idempotencyKey,
            ];

            if (! empty($this->apiKey)) {
                $headers['Authorization'] = 'Bearer '.$this->apiKey;
            }

            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->post($v1Url, $payload);

            if ($response->successful()) {
                Log::info("WhatsApp OTP sent successfully via WAG v1 to {$target}");

                return true;
            }

            Log::warning("WAG v1 returned HTTP {$response->status()}: {$response->body()}");
        } catch (\Throwable $e) {
            Log::error("Exception in WAG v1 send to {$target}: {$e->getMessage()}");
        }

        return false;
    }

    protected function sendMessageLegacy(string $url, string $target, string $message): bool
    {
        try {
            $payload = [
                'target' => $target,
                'phone' => $target,
                'number' => $target,
                'message' => $message,
            ];

            if (! empty($this->apiKey)) {
                $payload['token'] = $this->apiKey;
                $payload['api_key'] = $this->apiKey;
            }

            $response = Http::timeout(10)->post($url, $payload);

            if ($response->successful()) {
                Log::info("WhatsApp OTP sent successfully to {$target}");

                return true;
            }

            Log::error("Failed sending WhatsApp to {$target}: HTTP {$response->status()} - {$response->body()}");

            return false;
        } catch (\Throwable $e) {
            Log::error("Exception sending WhatsApp to {$target}: {$e->getMessage()}");

            return false;
        }
    }
}
