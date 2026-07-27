<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\AbsenHariIni;
use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use App\Filament\Resources\OvertimeRequests\OvertimeRequestResource;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->employee;
    }

    protected function getStats(): array
    {
        $employee = auth()->user()?->employee;
        if (! $employee) {
            return [];
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;

        $presentDays = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereIn('status', ['on_time', 'present'])
            ->count();

        $lateDays = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'late')
            ->count();

        $maxQuota = $employee->company?->policy?->annual_leave_quota ?? 12;
        $usedLeaveDays = (int) LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', 'annual_leave')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('days_count');

        $remainingLeave = max(0, $maxQuota - $usedLeaveDays);

        $overtimeMinutes = (int) OvertimeRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('duration_minutes');

        $overtimeHours = round($overtimeMinutes / 60, 1);

        return [
            Stat::make('Kehadiran Bulan Ini', "{$presentDays} Hari")
                ->description("Telat: {$lateDays} hari bulan ini")
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->url(AbsenHariIni::getUrl()),

            Stat::make('Kuota Cuti Tahunan', "Sisa {$remainingLeave} Hari")
                ->description("Terpakai: {$usedLeaveDays} dari {$maxQuota} hari")
                ->color($remainingLeave > 2 ? 'primary' : 'warning')
                ->icon('heroicon-o-calendar')
                ->url(LeaveRequestResource::getUrl('index')),

            Stat::make('Lembur Bulan Ini', "{$overtimeHours} Jam")
                ->description('Total durasi lembur disetujui')
                ->color('info')
                ->icon('heroicon-o-clock')
                ->url(OvertimeRequestResource::getUrl('index')),
        ];
    }
}
