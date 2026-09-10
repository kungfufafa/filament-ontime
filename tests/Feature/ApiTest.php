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
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ApiTest extends TestCase
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
            'name' => 'PT Test API',
            'code' => 'TESTAPI',
            'is_active' => true,
        ]);

        $this->division = Division::create([
            'company_id' => $this->company->id,
            'name' => 'IT',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $jobLevel = JobLevel::firstOrCreate([
            'name' => 'Staff',
            'level_order' => 1,
        ]);

        $jobTitle = JobTitle::firstOrCreate([
            'name' => 'Software Engineer',
            'division_id' => $this->division->id,
        ]);

        CompanyPolicy::create([
            'company_id' => $this->company->id,
            'late_tolerance_minutes' => 15,
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
            'geofence_radius_meters' => 100,
        ]);

        $this->employeeUser = User::factory()->create([
            'email' => 'employee.api@ontime.com',
            'password' => bcrypt('password'),
        ]);
        $this->employeeUser->assignRole($employeeRole);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'EMP.API.001',
            'full_name' => 'Employee API Test',
            'join_date' => now()->toDateString(),
            'status' => 'permanent',
        ]);
    }

    public function test_user_can_login_via_api_and_get_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'employee.api@ontime.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'token_type', 'user']);
    }

    public function test_employee_can_check_in_via_api(): void
    {
        $token = $this->employeeUser->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/attendance/check-in', []);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Check in berhasil');
    }
}
