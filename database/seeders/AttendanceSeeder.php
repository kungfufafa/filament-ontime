<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Freelancer;
use App\Models\Intern;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds for attendance records.
     */
    public function run(): void
    {
        $employees = Employee::pluck('id')->toArray();
        $interns = Intern::pluck('id')->toArray();
        $freelancers = Freelancer::pluck('id')->toArray();

        if (empty($employees) && empty($interns) && empty($freelancers)) {
            return;
        }

        Schema::disableForeignKeyConstraints();
        Attendance::truncate();
        Schema::enableForeignKeyConstraints();

        $statuses = ['on_time', 'on_time', 'late', 'on_time', 'leave'];
        $baseDate = today();

        // 1. Seed Employee Attendances
        for ($i = 0; $i < 20; $i++) {
            if (empty($employees)) {
                break;
            }
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

        // 2. Seed Intern Attendances
        foreach ($interns as $idx => $internId) {
            for ($d = 0; $d < 5; $d++) {
                $date = (clone $baseDate)->subDays($d);
                $status = ($d === 1) ? 'late' : 'on_time';
                $lateMinutes = ($status === 'late') ? 20 : 0;
                $checkIn = Carbon::parse($date->format('Y-m-d').' 07:'.rand(45, 59).':00');
                if ($status === 'late') {
                    $checkIn = Carbon::parse($date->format('Y-m-d').' 08:20:00');
                }
                $checkOut = Carbon::parse($date->format('Y-m-d').' 17:00:00');

                Attendance::create([
                    'intern_id' => $internId,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'check_in_lat' => -6.1753924,
                    'check_in_lng' => 106.8271528,
                    'check_out_lat' => -6.1753924,
                    'check_out_lng' => 106.8271528,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'notes' => $status === 'late' ? 'Terlambat 20 menit' : 'Presensi Magang',
                    'is_corrected' => false,
                ]);
            }
        }

        // 3. Seed Freelancer Attendances
        foreach ($freelancers as $idx => $freelancerId) {
            for ($d = 0; $d < 5; $d++) {
                $date = (clone $baseDate)->subDays($d);
                $status = 'on_time';
                $checkIn = Carbon::parse($date->format('Y-m-d').' 07:'.rand(45, 59).':00');
                $checkOut = Carbon::parse($date->format('Y-m-d').' 17:15:00');

                Attendance::create([
                    'freelancer_id' => $freelancerId,
                    'date' => $date->format('Y-m-d'),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'check_in_lat' => -6.1753924,
                    'check_in_lng' => 106.8271528,
                    'check_out_lat' => -6.1753924,
                    'check_out_lng' => 106.8271528,
                    'status' => $status,
                    'late_minutes' => 0,
                    'notes' => 'Presensi Freelance',
                    'is_corrected' => false,
                ]);
            }
        }
    }
}
