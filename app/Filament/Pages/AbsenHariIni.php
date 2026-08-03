<?php

namespace App\Filament\Pages;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\CompanyPolicy;
use App\Services\ApprovalFlowService;
use App\Services\FaceRecognitionService;
use App\Services\GeofenceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class AbsenHariIni extends Page
{
    protected static ?string $title = 'Presensi Mandiri';

    protected static ?string $navigationLabel = 'Presensi Mandiri';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-camera';

    protected static string|UnitEnum|null $navigationGroup = 'Presensi & Pengajuan';

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

    public function getTodayAttendanceProperty(): ?Attendance
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return Attendance::byWorker($user)->today()->first();
    }

    protected function getHeaderActions(): array
    {
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

    public function checkInAction(): Action
    {
        $policy = $this->companyPolicy;
        $requirePhoto = $policy?->require_photo ?? false;
        $requireGps = $policy?->require_gps ?? false;
        $requireFaceRecognition = $policy?->require_face_recognition ?? false;

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
                    ])
                    ->required($requirePhoto)
                    ->visible($requirePhoto)
                    ->validationMessages([
                        'required' => 'Foto selfie wajib diambil. Silakan klik tombol "Ambil Foto" terlebih dahulu.',
                    ]),

                ViewField::make('gps_capture')
                    ->view('filament.components.gps-capture', ['type' => 'check_in'])
                    ->visible($requireGps),

                Hidden::make('check_in_lat')
                    ->required($requireGps)
                    ->visible($requireGps)
                    ->validationMessages([
                        'required' => 'Koordinat GPS belum terdeteksi. Silakan klik Refresh GPS.',
                    ]),

                Hidden::make('check_in_lng')
                    ->required($requireGps)
                    ->visible($requireGps)
                    ->validationMessages([
                        'required' => 'Koordinat GPS belum terdeteksi. Silakan klik Refresh GPS.',
                    ]),
            ])
            ->action(function (array $data): void {
                $user = auth()->user();
                $employee = $user?->employee;
                $intern = $user?->intern;
                $freelancer = $user?->freelancer;

                // Tentukan profil yang digunakan
                $profile = $employee ?? $intern ?? $freelancer;
                if (! $profile) {
                    throw ValidationException::withMessages(['check_in' => 'Data profil tidak ditemukan untuk akun ini.']);
                }

                $policy = $this->companyPolicy;
                $company = $profile->company;

                // Face Recognition validation
                $isFaceVerified = null;
                $faceMatchScore = null;
                $faceVerificationNotes = null;
                $faceFailedAndRequiresApproval = false;

                if ($policy?->require_face_recognition) {
                    $faceResult = app(FaceRecognitionService::class)->verifyFace(
                        $data['check_in_photo'] ?? null,
                        $profile->master_face_photo ?? null,
                        $policy
                    );

                    $isFaceVerified = $faceResult['is_matched'];
                    $faceMatchScore = $faceResult['score'];
                    $faceVerificationNotes = $faceResult['notes'];

                    if (! $isFaceVerified) {
                        if ($policy->face_fail_action === 'reject') {
                            throw ValidationException::withMessages([
                                'check_in_photo' => 'Verifikasi Wajah Gagal: '.$faceVerificationNotes,
                            ]);
                        } else {
                            $faceFailedAndRequiresApproval = true;
                        }
                    }
                }

                // Multi-location Geofence validation
                $isOutOfBounds = false;
                if ($policy?->require_gps && ! empty($data['check_in_lat']) && ! empty($data['check_in_lng']) && $company) {
                    $result = GeofenceService::validateCompanyGeofence(
                        $company,
                        (float) $data['check_in_lat'],
                        (float) $data['check_in_lng']
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

                $requiresApproval = $isOutOfBounds || $faceFailedAndRequiresApproval;
                $status = $requiresApproval ? AttendanceStatus::PendingApproval : AttendanceStatus::OnTime;
                $lateMinutes = 0;

                if (! $requiresApproval && $now->greaterThan($shiftStartThreshold)) {
                    $status = AttendanceStatus::Late;
                    $lateMinutes = (int) $now->diffInMinutes(now()->setTimeFromTimeString($workStartTimeStr));
                }

                // Simpan attendance dengan kolom yang sesuai tipe profil
                $attendance = Attendance::create([
                    'employee_id' => $employee?->id,
                    'intern_id' => $intern?->id,
                    'freelancer_id' => $freelancer?->id,
                    'date' => today(),
                    'check_in' => $now,
                    'check_in_photo' => $data['check_in_photo'] ?? null,
                    'check_in_lat' => $data['check_in_lat'] ?? null,
                    'check_in_lng' => $data['check_in_lng'] ?? null,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'is_out_of_bounds' => $isOutOfBounds,
                    'is_face_verified' => $isFaceVerified,
                    'face_match_score' => $faceMatchScore,
                    'face_verification_notes' => $faceVerificationNotes,
                ]);

                if ($requiresApproval) {
                    app(ApprovalFlowService::class)->generateSteps($attendance, 'geofence');

                    $reasonNote = $faceFailedAndRequiresApproval ? 'Verifikasi wajah tidak cocok. ' : '';
                    $reasonNote .= $isOutOfBounds ? 'Lokasi berada di luar geofence.' : '';

                    Notification::make()
                        ->title('Check In Dikirim (Menunggu Persetujuan)')
                        ->body($reasonNote.' Presensi berhasil dicatat dan sedang menunggu persetujuan atasan.')
                        ->warning()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Check In Berhasil!')
                        ->body("Waktu Check In: {$now->format('H:i:s')} WIB (".($status === AttendanceStatus::Late ? "Terlambat {$lateMinutes} menit" : 'Tepat Waktu').')')
                        ->success()
                        ->send();
                }
            });
    }

    public function checkOutAction(): Action
    {
        $policy = $this->companyPolicy;
        $requirePhoto = $policy?->require_photo ?? false;
        $requireGps = $policy?->require_gps ?? false;
        $requireFaceRecognition = $policy?->require_face_recognition ?? false;

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
                    ])
                    ->required($requirePhoto)
                    ->visible($requirePhoto)
                    ->validationMessages([
                        'required' => 'Foto selfie wajib diambil. Silakan klik tombol "Ambil Foto" terlebih dahulu.',
                    ]),

                ViewField::make('gps_capture')
                    ->view('filament.components.gps-capture', ['type' => 'check_out'])
                    ->visible($requireGps),

                Hidden::make('check_out_lat')
                    ->required($requireGps)
                    ->visible($requireGps)
                    ->validationMessages([
                        'required' => 'Koordinat GPS belum terdeteksi. Silakan klik Refresh GPS.',
                    ]),

                Hidden::make('check_out_lng')
                    ->required($requireGps)
                    ->visible($requireGps)
                    ->validationMessages([
                        'required' => 'Koordinat GPS belum terdeteksi. Silakan klik Refresh GPS.',
                    ]),
            ])
            ->action(function (array $data): void {
                $attendance = $this->todayAttendance;
                if (! $attendance) {
                    throw ValidationException::withMessages(['check_out' => 'Anda belum melakukan Check In hari ini.']);
                }

                $policy = $this->companyPolicy;
                $profile = $this->getLinkedProfile();
                $company = $profile?->company;

                // Face Recognition validation
                $isFaceVerified = null;
                $faceMatchScore = null;
                $faceVerificationNotes = null;
                $faceFailedAndRequiresApproval = false;

                if ($policy?->require_face_recognition) {
                    $faceResult = app(FaceRecognitionService::class)->verifyFace(
                        $data['check_out_photo'] ?? null,
                        $profile->master_face_photo ?? null,
                        $policy
                    );

                    $isFaceVerified = $faceResult['is_matched'];
                    $faceMatchScore = $faceResult['score'];
                    $faceVerificationNotes = $faceResult['notes'];

                    if (! $isFaceVerified) {
                        if ($policy->face_fail_action === 'reject') {
                            throw ValidationException::withMessages([
                                'check_out_photo' => 'Verifikasi Wajah Gagal: '.$faceVerificationNotes,
                            ]);
                        } else {
                            $faceFailedAndRequiresApproval = true;
                        }
                    }
                }

                $isOutOfBounds = false;
                if ($policy?->require_gps && ! empty($data['check_out_lat']) && ! empty($data['check_out_lng']) && $company) {
                    $result = GeofenceService::validateCompanyGeofence(
                        $company,
                        (float) $data['check_out_lat'],
                        (float) $data['check_out_lng']
                    );

                    if (! $result['is_valid']) {
                        $isOutOfBounds = true;
                    }
                }

                $requiresApproval = $isOutOfBounds || $faceFailedAndRequiresApproval;
                $now = now();
                $updateData = [
                    'check_out' => $now,
                    'check_out_photo' => $data['check_out_photo'] ?? null,
                    'check_out_lat' => $data['check_out_lat'] ?? null,
                    'check_out_lng' => $data['check_out_lng'] ?? null,
                    'is_face_verified' => $isFaceVerified ?? $attendance->is_face_verified,
                    'face_match_score' => $faceMatchScore ?? $attendance->face_match_score,
                    'face_verification_notes' => $faceVerificationNotes ?? $attendance->face_verification_notes,
                ];

                if ($requiresApproval) {
                    $updateData['is_out_of_bounds'] = $isOutOfBounds || $attendance->is_out_of_bounds;
                    $updateData['status'] = AttendanceStatus::PendingApproval;
                }

                $attendance->update($updateData);

                if ($requiresApproval) {
                    if ($attendance->approvalSteps()->count() === 0) {
                        app(ApprovalFlowService::class)->generateSteps($attendance, 'geofence');
                    }

                    Notification::make()
                        ->title('Check Out Dikirim (Menunggu Persetujuan)')
                        ->body('Presensi Check Out sedang menunggu persetujuan atasan.')
                        ->warning()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Check Out Berhasil!')
                        ->body("Waktu Check Out: {$now->format('H:i:s')} WIB")
                        ->success()
                        ->send();
                }
            });
    }
}
