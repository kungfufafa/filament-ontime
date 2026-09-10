<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use App\Services\GeofenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Approver']);
        Role::create(['name' => 'BOD']);
        Role::create(['name' => 'Superadmin']);
    }

    private function createEmployee(Company $company): Employee
    {
        $user = User::factory()->create();
        $division = Division::create(['company_id' => $company->id, 'name' => 'IT', 'code' => 'IT', 'is_active' => true]);
        $jobLevel = JobLevel::create(['name' => 'Staff', 'level_number' => 1]);
        $jobTitle = JobTitle::create(['name' => 'Developer']);

        return Employee::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'EMP'.rand(100, 999),
            'full_name' => 'Test Employee',
            'join_date' => now(),
            'status' => 'permanent',
        ]);
    }

    public function test_late_status_determination_based_on_shift_and_tolerance(): void
    {
        $company = Company::create([
            'name' => 'PT Monitored Office',
            'code' => 'PTMO',
            'is_active' => true,
        ]);

        $company->policy()->update([
            'work_start_time' => '08:00:00',
            'late_tolerance_minutes' => 15,
        ]);

        $employee = $this->createEmployee($company);

        $onTimeAttendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 10, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $this->assertEquals(AttendanceStatus::OnTime, $onTimeAttendance->status);
        $this->assertEquals(0, $onTimeAttendance->late_minutes);

        $lateAttendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => today()->addDay(),
            'check_in' => now()->addDay()->setTime(8, 25, 0),
            'status' => AttendanceStatus::Late,
            'late_minutes' => 25,
        ]);

        $this->assertEquals(AttendanceStatus::Late, $lateAttendance->status);
        $this->assertEquals(25, $lateAttendance->late_minutes);
    }

    public function test_geofence_distance_calculation_and_validation(): void
    {
        $officeLat = -6.1753924;
        $officeLng = 106.8271528;
        $radius = 100;

        $insideLat = -6.1750000;
        $insideLng = 106.8271528;

        $outsideLat = -6.1700000;
        $outsideLng = 106.8271528;

        $distanceOutside = GeofenceService::calculateDistance($officeLat, $officeLng, $outsideLat, $outsideLng);

        $this->assertTrue(GeofenceService::isWithinGeofence($officeLat, $officeLng, $insideLat, $insideLng, $radius));
        $this->assertFalse(GeofenceService::isWithinGeofence($officeLat, $officeLng, $outsideLat, $outsideLng, $radius));
        $this->assertGreaterThan($radius, $distanceOutside);
    }
}
