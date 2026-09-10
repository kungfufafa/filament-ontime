<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyPolicy;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ApiEndpointsExtensionTest extends TestCase
{
    use RefreshDatabase;

    protected User $employeeUser;

    protected Employee $employee;

    protected Company $company;

    protected Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seed(RoleSeeder::class);

        $employeeRole = Role::findByName('Employee');

        $this->company = Company::create([
            'name' => 'PT Test Extension API',
            'code' => 'TESTEXTAPI',
            'is_active' => true,
        ]);

        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'late_tolerance_minutes' => 15,
            'require_photo' => false,
            'require_gps' => false,
            'annual_leave_quota' => 12,
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $this->division = Division::create([
            'company_id' => $this->company->id,
            'name' => 'Divisi IT',
            'code' => 'DIVIT',
            'is_active' => true,
        ]);

        $jobLevel = JobLevel::firstOrCreate(['name' => 'Staff', 'level_order' => 1]);
        $jobTitle = JobTitle::firstOrCreate(['name' => 'Programmer']);

        $this->employeeUser = User::create([
            'name' => 'Employee Extension Test',
            'email' => 'employee.ext@ontime.com',
            'password' => bcrypt('password'),
        ]);
        $this->employeeUser->assignRole($employeeRole);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'EMP.EXT.001',
            'full_name' => 'Employee Extension Test',
            'join_date' => now()->toDateString(),
            'status' => 'permanent',
        ]);
    }

    public function test_get_today_attendance_summary(): void
    {
        Sanctum::actingAs($this->employeeUser);

        $response = $this->getJson('/api/v1/attendance/today');

        $response->assertStatus(200)
            ->assertJsonPath('has_checked_in', false)
            ->assertJsonPath('has_checked_out', false)
            ->assertJsonPath('date', today()->toDateString());
    }

    public function test_delete_master_face(): void
    {
        Sanctum::actingAs($this->employeeUser);

        $this->employee->update([
            'master_face_photo' => 'master-faces/test.jpg',
            'master_face_verified_at' => now(),
        ]);

        $response = $this->deleteJson('/api/v1/attendance/master-face');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Foto Master Wajah berhasil dihapus.');

        $this->assertNull($this->employee->fresh()->master_face_photo);
    }
}
