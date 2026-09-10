<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasPresensiActions;
use App\Models\Attendance;
use App\Models\CompanyPolicy;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class AbsenHariIni extends Page
{
    use HasPresensiActions;

    protected static ?string $title = 'Presensi Mandiri';

    protected static ?string $navigationLabel = 'Presensi Mandiri';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-camera';

    protected static string|UnitEnum|null $navigationGroup = 'Presensi';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.absen-hari-ini';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user || $user->hasRole('Superadmin')) {
            return false;
        }

        return (bool) ($user->employee || $user->intern || $user->freelancer);
    }

    protected function getHeaderActions(): array
    {
        $policy = $this->companyPolicy;

        if (! ($policy?->require_face_recognition ?? false)) {
            return [];
        }

        $profile = $this->getLinkedProfile();

        if (! empty($profile?->master_face_photo)) {
            return [];
        }

        return [
            $this->updateMasterFaceAction(),
        ];
    }

    public function updateMasterFaceAction(): Action
    {
        $profile = $this->getLinkedProfile();
        $hasMasterPhoto = ! empty($profile?->master_face_photo);

        return Action::make('updateMasterFace')
            ->label($hasMasterPhoto ? 'Ubah Foto Master' : 'Foto Master Wajah')
            ->icon($hasMasterPhoto ? 'heroicon-o-pencil-square' : 'heroicon-o-user-circle')
            ->color($hasMasterPhoto ? 'gray' : 'info')
            ->modalHeading($hasMasterPhoto ? 'Ubah Foto Master Wajah' : 'Pendaftaran Foto Master Wajah')
            ->modalDescription('Unggah foto dari galeri/penyimpanan atau ambil foto via kamera langsung sebagai referensi biometrik Anda.')
            ->mountUsing(function ($form) {
                $profile = $this->getLinkedProfile();
                $photo = $profile?->master_face_photo;
                if ($photo) {
                    $disk = config('filesystems.default');
                    if (Storage::disk($disk)->exists($photo) || Storage::disk('public')->exists($photo)) {
                        $form->fill([
                            'master_face_photo' => $photo,
                        ]);
                    } else {
                        $form->fill([
                            'master_face_photo' => null,
                        ]);
                    }
                }
            })
            ->schema([
                ViewField::make('master_face_photo')
                    ->label('Foto Master Wajah')
                    ->view('filament.components.master-face-capture')
                    ->required()
                    ->validationMessages([
                        'required' => 'Foto Master Wajah wajib diunggah atau diambil via kamera.',
                    ]),
            ])
            ->action(function (array $data): void {
                $profile = $this->getLinkedProfile();

                if (! $profile) {
                    Notification::make()
                        ->title('Profil Tidak Ditemukan')
                        ->body('Akun Anda belum terhubung dengan data Karyawan / Magang / Freelancer.')
                        ->danger()
                        ->send();

                    return;
                }

                $profile->update([
                    'master_face_photo' => $data['master_face_photo'],
                    'master_face_verified_at' => now(),
                ]);

                Notification::make()
                    ->title('Foto Master Wajah Berhasil Disimpan!')
                    ->body('Foto master biometrik Anda telah terdaftar dan diperbarui.')
                    ->success()
                    ->send();
            });
    }

    protected function getLinkedProfile(): mixed
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

    public function checkInAction(): Action
    {
        $policy = $this->companyPolicy;
        $requirePhoto = $policy?->require_photo ?? false;
        $requireGps = $policy?->require_gps ?? false;
        $requireFaceRecognition = $policy?->require_face_recognition ?? false;
        $isPhotoNeeded = $requirePhoto || $requireFaceRecognition;

        return Action::make('checkIn')
            ->label('Check In Sekarang')
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->color('success')
            ->modalHeading('Check In Absensi Hari Ini')
            ->schema([
                ViewField::make('check_in_photo')
                    ->label('Foto Selfie Check In')
                    ->view('filament.components.camera-capture', [
                        'requireFaceRecognition' => $requireFaceRecognition,
                        'requireGps' => $requireGps,
                        'mode' => 'check_in',
                    ])
                    ->required($isPhotoNeeded)
                    ->visible($isPhotoNeeded)
                    ->validationMessages([
                        'required' => 'Foto selfie wajib diambil. Silakan klik tombol "Check In".',
                    ]),

                ViewField::make('gps_capture')
                    ->view('filament.components.gps-capture', ['type' => 'check_in'])
                    ->visible($requireGps),

                Hidden::make('check_in_lat')
                    ->required($requireGps)
                    ->visible($requireGps),

                Hidden::make('check_in_lng')
                    ->required($requireGps)
                    ->visible($requireGps),
            ])
            ->action(function (array $data): void {
                $this->processCheckIn(
                    $data['check_in_photo'] ?? null,
                    isset($data['check_in_lat']) ? (float) $data['check_in_lat'] : null,
                    isset($data['check_in_lng']) ? (float) $data['check_in_lng'] : null
                );
            });
    }

    public function checkOutAction(): Action
    {
        $policy = $this->companyPolicy;
        $requirePhoto = $policy?->require_photo ?? false;
        $requireGps = $policy?->require_gps ?? false;
        $requireFaceRecognition = $policy?->require_face_recognition ?? false;
        $isPhotoNeeded = $requirePhoto || $requireFaceRecognition;

        return Action::make('checkOut')
            ->label('Check Out Sekarang')
            ->icon('heroicon-o-arrow-left-on-rectangle')
            ->color('danger')
            ->modalHeading('Check Out Absensi Hari Ini')
            ->schema([
                ViewField::make('check_out_photo')
                    ->label('Foto Selfie Check Out')
                    ->view('filament.components.camera-capture', [
                        'requireFaceRecognition' => $requireFaceRecognition,
                        'requireGps' => $requireGps,
                        'mode' => 'check_out',
                    ])
                    ->required($isPhotoNeeded)
                    ->visible($isPhotoNeeded)
                    ->validationMessages([
                        'required' => 'Foto selfie wajib diambil. Silakan klik tombol "Check Out".',
                    ]),

                ViewField::make('gps_capture')
                    ->view('filament.components.gps-capture', ['type' => 'check_out'])
                    ->visible($requireGps),

                Hidden::make('check_out_lat')
                    ->required($requireGps)
                    ->visible($requireGps),

                Hidden::make('check_out_lng')
                    ->required($requireGps)
                    ->visible($requireGps),
            ])
            ->action(function (array $data): void {
                $this->processCheckOut(
                    $data['check_out_photo'] ?? null,
                    isset($data['check_out_lat']) ? (float) $data['check_out_lat'] : null,
                    isset($data['check_out_lng']) ? (float) $data['check_out_lng'] : null
                );
            });
    }
}
