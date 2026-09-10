<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\AbsenHariIni;
use App\Models\Attendance;
use Carbon\Carbon;
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

        $attendanceStats = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->selectRaw("
                SUM(CASE WHEN status IN ('on_time', 'present') THEN 1 ELSE 0 END) as on_time_days,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                SUM(late_minutes) as total_late_minutes
            ")
            ->first();

        $onTimeDays = (int) ($attendanceStats?->on_time_days ?? 0);
        $lateDays = (int) ($attendanceStats?->late_days ?? 0);
        $totalPresent = $onTimeDays + $lateDays;
        $totalLateMinutes = (int) ($attendanceStats?->total_late_minutes ?? 0);

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        $todayStatusLabel = 'Belum Absen';
        $todayColor = 'gray';
        if ($todayAttendance) {
            $checkInTime = $todayAttendance->check_in ? Carbon::parse($todayAttendance->check_in)->format('H:i') : '—';
            $todayStatusLabel = "Check In: {$checkInTime}";
            $todayColor = $todayAttendance->status?->getColor() ?? 'success';
        }

        return [
            Stat::make('Total Kehadiran Bulan Ini', "{$totalPresent} Hari")
                ->description("Tepat waktu: {$onTimeDays} hari")
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->url(AbsenHariIni::getUrl()),

            Stat::make('Keterlambatan Bulan Ini', "{$lateDays} Hari")
                ->description("Total akumulasi: {$totalLateMinutes} menit")
                ->color($lateDays > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock')
                ->url(AbsenHariIni::getUrl()),

            Stat::make('Presensi Hari Ini', $todayStatusLabel)
                ->description($todayAttendance ? ($todayAttendance->check_out ? 'Sudah Check Out' : 'Sedang Bekerja') : 'Klik untuk presensi')
                ->color($todayColor)
                ->icon('heroicon-o-camera')
                ->url(AbsenHariIni::getUrl()),
        ];
    }
}
