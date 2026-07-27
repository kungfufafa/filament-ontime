<?php

namespace App\Services;

use App\Models\User;
use App\Models\WhatsappOtp;
use Carbon\Carbon;

class OtpService
{
    public function __construct(
        protected WhatsAppGatewayService $gatewayService
    ) {}

    /**
     * Request OTP for a given phone number and send it via Gateway Hub.
     */
    public function requestOtp(string $phone): array
    {
        $user = User::findByPhone($phone);

        if (! $user) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tidak terdaftar dalam sistem.',
            ];
        }

        if (! $user->is_active) {
            return [
                'success' => false,
                'message' => 'Akun pengguna ini sedang tidak aktif.',
            ];
        }

        $normalizedPhone = $this->gatewayService->normalizePhoneNumber($phone);

        // Rate limiting check (cooldown 60 seconds)
        $recentOtp = WhatsappOtp::where('phone', $normalizedPhone)
            ->where('created_at', '>=', Carbon::now()->subSeconds(60))
            ->first();

        if ($recentOtp) {
            $secondsLeft = 60 - Carbon::now()->diffInSeconds($recentOtp->created_at);

            return [
                'success' => false,
                'message' => "Tunggu {$secondsLeft} detik sebelum meminta kode OTP baru.",
                'cooldown' => $secondsLeft,
            ];
        }

        // Invalidate old OTPs for this phone number
        WhatsappOtp::where('phone', $normalizedPhone)
            ->whereNull('verified_at')
            ->delete();

        // Generate 6-digit numeric OTP
        $otp = (string) random_int(100000, 999999);

        // Store in database
        WhatsappOtp::create([
            'phone' => $normalizedPhone,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // Send message via Gateway Hub
        $message = "Kode OTP Login OnTime Anda adalah: *{$otp}*\n\nKode ini berlaku selama 5 menit. JANGAN BAGIKAN KODE INI KEPADA SIAPAPUN.";
        $sent = $this->gatewayService->sendMessage($phone, $message);

        if (! $sent) {
            return [
                'success' => false,
                'message' => 'Gagal mengirimkan kode OTP via WhatsApp. Pastikan gateway server aktif.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Kode OTP berhasil dikirimkan ke WhatsApp Anda.',
            'phone' => $normalizedPhone,
        ];
    }

    /**
     * Verify OTP code and return the authenticated User if valid.
     */
    public function verifyOtp(string $phone, string $otpCode): ?User
    {
        $normalizedPhone = $this->gatewayService->normalizePhoneNumber($phone);
        $otpDigits = trim($otpCode);

        $otpRecord = WhatsappOtp::where('phone', $normalizedPhone)
            ->where('otp', $otpDigits)
            ->whereNull('verified_at')
            ->where('expires_at', '>=', Carbon::now())
            ->latest()
            ->first();

        if (! $otpRecord) {
            return null;
        }

        // Mark OTP as verified
        $otpRecord->update(['verified_at' => Carbon::now()]);

        return User::findByPhone($phone);
    }
}
