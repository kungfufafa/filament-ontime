<?php

namespace App\Filament\Concerns;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\CompanyPolicy;
use App\Services\FaceRecognitionService;
use App\Services\GeofenceService;
use Filament\Notifications\Notification;

trait HasPresensiActions
{
    public function getTodayAttendanceProperty(): ?Attendance
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return Attendance::byWorker($user)->today()->first();
    }

    public function getLinkedProfile(): mixed
    {
        $user = auth()->user();

        return $user?->employee ?? $user?->intern ?? $user?->freelancer;
    }

    public function getCompanyPolicyProperty(): ?CompanyPolicy
    {
        $profile = $this->getLinkedProfile();

        return $profile?->company?->policy;
    }

    public function processCheckIn(?string $photoPath = null, ?float $lat = null, ?float $lng = null): void
    {
        $policy = $this->companyPolicy;
        $requirePhoto = $policy?->require_photo ?? false;
        $requireGps = $policy?->require_gps ?? false;
        $requireFaceRecognition = $policy?->require_face_recognition ?? false;
        $isPhotoNeeded = $requirePhoto || $requireFaceRecognition;

        if ($isPhotoNeeded && empty($photoPath)) {
            Notification::make()
                ->title('Foto Selfie Wajib')
                ->body('Foto selfie tidak terdeteksi. Silakan coba lagi.')
                ->danger()
                ->send();

            return;
        }

        if ($requireGps && (empty($lat) || empty($lng))) {
            Notification::make()
                ->title('Sinyal GPS Diperlukan')
                ->body('Koordinat GPS belum terdeteksi. Pastikan izin lokasi diaktifkan di browser Anda.')
                ->danger()
                ->send();

            return;
        }

        $profile = $this->getLinkedProfile();
        if (! $profile) {
            Notification::make()
                ->title('Profil Tidak Ditemukan')
                ->body('Data profil tidak ditemukan untuk akun ini.')
                ->danger()
                ->send();

            return;
        }

        $user = auth()->user();
        $employee = $user?->employee;
        $intern = $user?->intern;
        $freelancer = $user?->freelancer;
        $company = $profile->company;

        // Face Recognition validation
        $isFaceVerified = null;
        $faceMatchScore = null;
        $faceVerificationNotes = null;
        $faceFailedAndRequiresApproval = false;

        if ($policy?->require_face_recognition) {
            if (empty($profile->master_face_photo)) {
                Notification::make()
                    ->title('Foto Master Wajah Belum Terdaftar')
                    ->body('Silakan daftarkan Foto Master Wajah Anda terlebih dahulu.')
                    ->warning()
                    ->send();

                return;
            }

            $faceResult = app(FaceRecognitionService::class)->verifyFace(
                $photoPath,
                $profile->master_face_photo ?? null,
                $policy
            );

            $isFaceVerified = $faceResult['is_matched'];
            $faceMatchScore = $faceResult['score'];
            $faceVerificationNotes = $faceResult['notes'];

            if (! $isFaceVerified) {
                if ($policy->face_fail_action === 'reject') {
                    Notification::make()
                        ->title('Verifikasi Wajah Gagal')
                        ->body($faceVerificationNotes)
                        ->danger()
                        ->send();

                    return;
                } else {
                    $faceFailedAndRequiresApproval = true;
                }
            }
        }

        // Multi-location Geofence validation
        $isOutOfBounds = false;
        if ($policy?->require_gps && ! empty($lat) && ! empty($lng) && $company) {
            $result = GeofenceService::validateCompanyGeofence(
                $company,
                (float) $lat,
                (float) $lng
            );

            if (! $result['is_valid']) {
                $isOutOfBounds = true;
            }
        }

        // Late calculation
        $now = now();
        $workStartTimeStr = $policy?->work_start_time ?? '08:00:00';
        $lateToleranceMinutes = $policy?->late_tolerance_minutes ?? 15;

        $shiftStartThreshold = now()->setTimeFromTimeString($workStartTimeStr)->addMinutes($lateToleranceMinutes);

        $status = AttendanceStatus::OnTime;
        $lateMinutes = 0;

        if ($now->greaterThan($shiftStartThreshold)) {
            $status = AttendanceStatus::Late;
            $lateMinutes = (int) $now->diffInMinutes(now()->setTimeFromTimeString($workStartTimeStr));
        }

        $attendance = Attendance::create([
            'employee_id' => $employee?->id,
            'intern_id' => $intern?->id,
            'freelancer_id' => $freelancer?->id,
            'date' => today(),
            'check_in' => $now,
            'check_in_photo' => $photoPath,
            'check_in_lat' => $lat,
            'check_in_lng' => $lng,
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'is_out_of_bounds' => $isOutOfBounds,
            'is_face_verified' => $isFaceVerified,
            'face_match_score' => $faceMatchScore,
            'face_verification_notes' => $faceVerificationNotes,
        ]);

        $statusNote = $status === AttendanceStatus::Late ? "Terlambat {$lateMinutes} menit" : 'Tepat Waktu';
        $bodyNote = "Waktu Check In: {$now->format('H:i:s')} WIB ({$statusNote})";

        if ($isOutOfBounds) {
            $bodyNote .= ' - Catatan: Lokasi berada di luar radius kantor.';
        }

        Notification::make()
            ->title('Check In Berhasil!')
            ->body($bodyNote)
            ->color($isOutOfBounds ? 'warning' : 'success')
            ->send();
    }

    public function processCheckOut(?string $photoPath = null, ?float $lat = null, ?float $lng = null): void
    {
        $attendance = $this->todayAttendance;
        if (! $attendance) {
            Notification::make()
                ->title('Check Out Gagal')
                ->body('Anda belum melakukan Check In hari ini.')
                ->danger()
                ->send();

            return;
        }

        $policy = $this->companyPolicy;
        $requirePhoto = $policy?->require_photo ?? false;
        $requireGps = $policy?->require_gps ?? false;
        $requireFaceRecognition = $policy?->require_face_recognition ?? false;
        $isPhotoNeeded = $requirePhoto || $requireFaceRecognition;

        if ($isPhotoNeeded && empty($photoPath)) {
            Notification::make()
                ->title('Foto Selfie Wajib')
                ->body('Foto selfie tidak terdeteksi. Silakan coba lagi.')
                ->danger()
                ->send();

            return;
        }

        if ($requireGps && (empty($lat) || empty($lng))) {
            Notification::make()
                ->title('Sinyal GPS Diperlukan')
                ->body('Koordinat GPS belum terdeteksi. Pastikan izin lokasi diaktifkan di browser Anda.')
                ->danger()
                ->send();

            return;
        }

        $profile = $this->getLinkedProfile();
        $company = $profile?->company;

        // Face Recognition validation
        $isFaceVerified = null;
        $faceMatchScore = null;
        $faceVerificationNotes = null;
        $faceFailedAndRequiresApproval = false;

        if ($policy?->require_face_recognition) {
            if (empty($profile->master_face_photo)) {
                Notification::make()
                    ->title('Foto Master Wajah Belum Terdaftar')
                    ->body('Silakan daftarkan Foto Master Wajah Anda terlebih dahulu.')
                    ->warning()
                    ->send();

                return;
            }

            $faceResult = app(FaceRecognitionService::class)->verifyFace(
                $photoPath,
                $profile->master_face_photo ?? null,
                $policy
            );

            $isFaceVerified = $faceResult['is_matched'];
            $faceMatchScore = $faceResult['score'];
            $faceVerificationNotes = $faceResult['notes'];

            if (! $isFaceVerified) {
                if ($policy->face_fail_action === 'reject') {
                    Notification::make()
                        ->title('Verifikasi Wajah Gagal')
                        ->body($faceVerificationNotes)
                        ->danger()
                        ->send();

                    return;
                } else {
                    $faceFailedAndRequiresApproval = true;
                }
            }
        }

        $isOutOfBounds = false;
        if ($policy?->require_gps && ! empty($lat) && ! empty($lng) && $company) {
            $result = GeofenceService::validateCompanyGeofence(
                $company,
                (float) $lat,
                (float) $lng
            );

            if (! $result['is_valid']) {
                $isOutOfBounds = true;
            }
        }

        $now = now();
        $updateData = [
            'check_out' => $now,
            'check_out_photo' => $photoPath,
            'check_out_lat' => $lat,
            'check_out_lng' => $lng,
            'is_out_of_bounds' => $isOutOfBounds || $attendance->is_out_of_bounds,
            'is_face_verified' => $isFaceVerified ?? $attendance->is_face_verified,
            'face_match_score' => $faceMatchScore ?? $attendance->face_match_score,
            'face_verification_notes' => $faceVerificationNotes ?? $attendance->face_verification_notes,
        ];

        $attendance->update($updateData);

        $bodyNote = "Waktu Check Out: {$now->format('H:i:s')} WIB";
        if ($isOutOfBounds) {
            $bodyNote .= ' - Catatan: Lokasi berada di luar radius kantor.';
        }

        Notification::make()
            ->title('Check Out Berhasil!')
            ->body($bodyNote)
            ->color($isOutOfBounds ? 'warning' : 'success')
            ->send();
    }
}
