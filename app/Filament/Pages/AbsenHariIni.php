<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\CompanyPolicy;
use App\Services\GeofenceService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\ValidationException;
use UnitEnum;

class AbsenHariIni extends Page
{
    protected static ?string $title = 'Absen Hari Ini';

    protected static ?string $navigationLabel = 'Absen Hari Ini';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-camera';

    protected static string|UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.absen-hari-ini';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ! $user->hasRole('Superadmin') && $user->employee);
    }

    public function getTodayAttendanceProperty(): ?Attendance
    {
        $employee = auth()->user()?->employee;
        if (! $employee) {
            return null;
        }

        return Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();
    }

    public function getCompanyPolicyProperty(): ?CompanyPolicy
    {
        return auth()->user()?->employee?->company?->policy;
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
                    ->visible($requirePhoto),

                ViewField::make('gps_capture')
                    ->view('filament.components.gps-capture')
                    ->visible($requireGps),

                TextInput::make('check_in_lat')
                    ->label('Latitude GPS')
                    ->numeric()
                    ->readOnly()
                    ->required($requireGps)
                    ->visible($requireGps)
                    ->placeholder('Mengambil GPS otomatis...')
                    ->helperText('Lokasi GPS diambil secara otomatis dari perangkat Anda.'),

                TextInput::make('check_in_lng')
                    ->label('Longitude GPS')
                    ->numeric()
                    ->readOnly()
                    ->required($requireGps)
                    ->visible($requireGps)
                    ->placeholder('Mengambil GPS otomatis...')
                    ->helperText('Lokasi GPS diambil secara otomatis dari perangkat Anda.'),
            ])
            ->action(function (array $data): void {
                $employee = auth()->user()?->employee;
                if (! $employee) {
                    throw ValidationException::withMessages(['check_in' => 'Data Karyawan tidak ditemukan untuk akun ini.']);
                }

                $policy = $this->companyPolicy;
                $company = $employee->company;

                // Geofence validation
                if ($policy?->require_gps && ! empty($data['check_in_lat']) && ! empty($data['check_in_lng'])) {
                    $companyLat = (float) ($company->latitude ?? 0);
                    $companyLng = (float) ($company->longitude ?? 0);
                    $radius = $policy->geofence_radius_meters ?? 100;

                    if ($companyLat != 0 && $companyLng != 0) {
                        $distance = GeofenceService::calculateDistance(
                            $companyLat,
                            $companyLng,
                            (float) $data['check_in_lat'],
                            (float) $data['check_in_lng']
                        );

                        if ($distance > $radius) {
                            throw ValidationException::withMessages([
                                'check_in_lat' => "Posisi Anda ({$distance} meter) berada di luar radius geofence kantor ({$radius} meter).",
                            ]);
                        }
                    }
                }

                // Late calculation
                $now = now();
                $workStartTimeStr = $policy?->work_start_time ?? '08:00:00';
                $lateToleranceMinutes = $policy?->late_tolerance_minutes ?? 15;

                $shiftStartThreshold = now()->setTimeFromTimeString($workStartTimeStr)->addMinutes($lateToleranceMinutes);

                $status = 'on_time';
                $lateMinutes = 0;

                if ($now->greaterThan($shiftStartThreshold)) {
                    $status = 'late';
                    $lateMinutes = (int) $now->diffInMinutes(now()->setTimeFromTimeString($workStartTimeStr));
                }

                Attendance::create([
                    'employee_id' => $employee->id,
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
                    ->body("Waktu Check In: {$now->format('H:i:s')} (".($status === 'late' ? "Terlambat {$lateMinutes} menit" : 'Tepat Waktu').')')
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
                    ->visible($requirePhoto),

                ViewField::make('gps_capture')
                    ->view('filament.components.gps-capture')
                    ->visible($requireGps),

                TextInput::make('check_out_lat')
                    ->label('Latitude GPS')
                    ->numeric()
                    ->readOnly()
                    ->required($requireGps)
                    ->visible($requireGps)
                    ->placeholder('Mengambil GPS otomatis...')
                    ->helperText('Lokasi GPS diambil secara otomatis dari perangkat Anda.'),

                TextInput::make('check_out_lng')
                    ->label('Longitude GPS')
                    ->numeric()
                    ->readOnly()
                    ->required($requireGps)
                    ->visible($requireGps)
                    ->placeholder('Mengambil GPS otomatis...')
                    ->helperText('Lokasi GPS diambil secara otomatis dari perangkat Anda.'),
            ])
            ->action(function (array $data): void {
                $attendance = $this->todayAttendance;
                if (! $attendance) {
                    throw ValidationException::withMessages(['check_out' => 'Anda belum melakukan Check In hari ini.']);
                }

                $policy = $this->companyPolicy;
                $company = auth()->user()?->employee?->company;

                if ($policy?->require_gps && ! empty($data['check_out_lat']) && ! empty($data['check_out_lng'])) {
                    $companyLat = (float) ($company->latitude ?? 0);
                    $companyLng = (float) ($company->longitude ?? 0);
                    $radius = $policy->geofence_radius_meters ?? 100;

                    if ($companyLat != 0 && $companyLng != 0) {
                        $distance = GeofenceService::calculateDistance(
                            $companyLat,
                            $companyLng,
                            (float) $data['check_out_lat'],
                            (float) $data['check_out_lng']
                        );

                        if ($distance > $radius) {
                            throw ValidationException::withMessages([
                                'check_out_lat' => "Posisi Anda ({$distance} meter) berada di luar radius geofence kantor ({$radius} meter).",
                            ]);
                        }
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
                    ->body("Waktu Check Out: {$now->format('H:i:s')}")
                    ->success()
                    ->send();
            });
    }
}
