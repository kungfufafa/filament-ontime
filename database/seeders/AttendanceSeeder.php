<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds for exactly 20 attendance records.
     */
    public function run(): void
    {
        $employees = Employee::pluck('id')->toArray();

        if (empty($employees)) {
            return;
        }

        Attendance::truncate();

        $statuses = ['on_time', 'on_time', 'late', 'on_time', 'leave'];
        $baseDate = today();

        for ($i = 0; $i < 20; $i++) {
            $employeeId = $employees[$i % count($employees)];
            $date = (clone $baseDate)->subDays((int) floor($i / 2));
            $status = $statuses[$i % count($statuses)];

            $checkIn = null;
            $checkOut = null;
            $lateMinutes = 0;
            $notes = null;

            if ($status === 'on_time') {
                $checkIn = Carbon::parse($date->format('Y-m-d').' 07:'.rand(45, 59).':00');
                $checkOut = Carbon::parse($date->format('Y-m-d').' 17:'.rand(0, 30).':00');
                $notes = 'Hadir Tepat Waktu';
            } elseif ($status === 'late') {
                $lateMinutes = rand(16, 45);
                $checkIn = Carbon::parse($date->format('Y-m-d').' 08:'.$lateMinutes.':00');
                $checkOut = Carbon::parse($date->format('Y-m-d').' 17:'.rand(5, 25).':00');
                $notes = "Terlambat {$lateMinutes} menit";
            } else {
                $notes = 'Izin / Cuti Tahunan';
            }

            Attendance::create([
                'employee_id' => $employeeId,
                'date' => $date->format('Y-m-d'),
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'check_in_lat' => $checkIn ? -6.1753924 : null,
                'check_in_lng' => $checkIn ? 106.8271528 : null,
                'check_out_lat' => $checkOut ? -6.1753924 : null,
                'check_out_lng' => $checkOut ? 106.8271528 : null,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'notes' => $notes,
                'is_corrected' => false,
            ]);
        }
    }
}
