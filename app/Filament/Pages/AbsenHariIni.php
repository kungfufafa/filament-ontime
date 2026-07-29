<?php

namespace App\Filament\Pages;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\CompanyPolicy;
use App\Services\GeofenceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
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

        return Action::make('checkIn')
            ->label('Check In Sekarang')
            ->icon('heroicon-o-arrow-right-on-rectangle')
            ->color('success')
            ->modalHeading('Check In Absensi Hari Ini')
            ->schema([
                ViewField::make('check_in_photo')
                    ->label('Foto Selfie Check In')
                    ->view('filament.components.camera-capture')
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

                // Multi-location Geofence validation
                if ($policy?->require_gps && ! empty($data['check_in_lat']) && ! empty($data['check_in_lng']) && $company) {
                    $result = GeofenceService::validateCompanyGeofence(
                        $company,
                        (float) $data['check_in_lat'],
                        (float) $data['check_in_lng']
                    );

                    if (! $result['is_valid']) {
                        Notification::make()
                            ->title('Lokasi Di Luar Geofence')
                            ->body($result['message'])
                            ->danger()
                            ->send();

                        throw ValidationException::withMessages([
                            'check_in_photo' => $result['message'],
                        ]);
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

                // Simpan attendance dengan kolom yang sesuai tipe profil
                Attendance::create([
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
                ]);

                Notification::make()
                    ->title('Check In Berhasil!')
                    ->body("Waktu Check In: {$now->format('H:i:s')} WIB (".($status === AttendanceStatus::Late ? "Terlambat {$lateMinutes} menit" : 'Tepat Waktu').')')
                    ->success()
                    ->send();
            });
    }

    public function checkOutAction(): Action
    {
        $policy = $this->companyPolicy;
        $requirePhoto = $policy?->require_photo ?? false;
        $requireGps = $policy?->require_gps ?? false;

        return Action::make('checkOut')
            ->label('Check Out Sekarang')
            ->icon('heroicon-o-arrow-left-on-rectangle')
            ->color('danger')
            ->modalHeading('Check Out Absensi Hari Ini')
            ->schema([
                ViewField::make('check_out_photo')
                    ->label('Foto Selfie Check Out')
                    ->view('filament.components.camera-capture')
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

                if ($policy?->require_gps && ! empty($data['check_out_lat']) && ! empty($data['check_out_lng']) && $company) {
                    $result = GeofenceService::validateCompanyGeofence(
                        $company,
                        (float) $data['check_out_lat'],
                        (float) $data['check_out_lng']
                    );

                    if (! $result['is_valid']) {
                        Notification::make()
                            ->title('Lokasi Di Luar Geofence')
                            ->body($result['message'])
                            ->danger()
                            ->send();

                        throw ValidationException::withMessages([
                            'check_out_photo' => $result['message'],
                        ]);
                    }
                }

                $now = now();
                $attendance->update([
                    'check_out' => $now,
                    'check_out_photo' => $data['check_out_photo'] ?? null,
                    'check_out_lat' => $data['check_out_lat'] ?? null,
                    'check_out_lng' => $data['check_out_lng'] ?? null,
                ]);

                Notification::make()
                    ->title('Check Out Berhasil!')
                    ->body("Waktu Check Out: {$now->format('H:i:s')} WIB")
                    ->success()
                    ->send();
            });
    }
}
