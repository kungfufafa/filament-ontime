<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ApprovalSaya;
use App\Filament\Pages\LaporanAbsensi;
use App\Filament\Resources\CompanyResource;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Company;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
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
                SUM(CASE WHEN status IN ('on_time', 'present') THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as leave_count
            ")
            ->first();

        $presentCount = (int) ($attendanceCounts?->present_count ?? 0);
        $lateCount = (int) ($attendanceCounts?->late_count ?? 0);
        $leaveCount = (int) ($attendanceCounts?->leave_count ?? 0);

        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $pendingOvertimes = OvertimeRequest::where('status', 'pending')->count();
        $pendingCorrections = AttendanceCorrection::where('status', 'pending')->count();
        $totalPending = $pendingLeaves + $pendingOvertimes + $pendingCorrections;

        $totalCompanies = Company::where('is_active', true)->count();

        return [
            Stat::make('Perusahaan Aktif', "{$totalCompanies} Badan Usaha")
                ->description('Total perusahaan terdaftar dalam sistem')
                ->color('primary')
                ->icon('heroicon-o-building-office-2')
                ->url(CompanyResource::getUrl('index')),

            Stat::make('Hadir Hari Ini', "{$presentCount} Karyawan")
                ->description("Telat: {$lateCount} | Cuti/Izin: {$leaveCount}")
                ->color('success')
                ->icon('heroicon-o-user-group')
                ->url(LaporanAbsensi::getUrl()),

            Stat::make('Pending Approval Lintas Company', "{$totalPending} Pengajuan")
                ->description("Cuti: {$pendingLeaves} | Lembur: {$pendingOvertimes} | Koreksi: {$pendingCorrections}")
                ->color($totalPending > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-inbox-stack')
                ->url(ApprovalSaya::getUrl()),
        ];
    }
}
