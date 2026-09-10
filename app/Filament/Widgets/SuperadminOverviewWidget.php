<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\LaporanAbsensi;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\EmployeeResource;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperadminOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasRole('Superadmin');
    }

    protected function getStats(): array
    {
        $today = today();

        $attendanceCounts = Attendance::query()
            ->whereDate('date', $today)
            ->selectRaw("
                SUM(CASE WHEN status IN ('on_time', 'present') THEN 1 ELSE 0 END) as on_time_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN is_out_of_bounds = 1 THEN 1 ELSE 0 END) as out_of_bounds_count
            ")
            ->first();

        $onTimeCount = (int) ($attendanceCounts?->on_time_count ?? 0);
        $lateCount = (int) ($attendanceCounts?->late_count ?? 0);
        $totalPresentToday = $onTimeCount + $lateCount;
        $outOfBoundsCount = (int) ($attendanceCounts?->out_of_bounds_count ?? 0);

        $totalCompanies = Company::where('is_active', true)->count();
        $totalEmployees = Employee::where('status', 'active')->count();

        return [
            Stat::make('Perusahaan Aktif', "{$totalCompanies} Badan Usaha")
                ->description('Total perusahaan terdaftar dalam sistem')
                ->color('primary')
                ->icon('heroicon-o-building-office-2')
                ->url(CompanyResource::getUrl('index')),

            Stat::make('Hadir Hari Ini', "{$totalPresentToday} Orang")
                ->description("Tepat waktu: {$onTimeCount} | Telat: {$lateCount}")
                ->color('success')
                ->icon('heroicon-o-user-group')
                ->url(LaporanAbsensi::getUrl()),

            Stat::make('Karyawan Aktif', "{$totalEmployees} Pegawai")
                ->description("Presensi luar radius hari ini: {$outOfBoundsCount}")
                ->color($outOfBoundsCount > 0 ? 'warning' : 'info')
                ->icon('heroicon-o-identification')
                ->url(EmployeeResource::getUrl('index')),
        ];
    }
}
