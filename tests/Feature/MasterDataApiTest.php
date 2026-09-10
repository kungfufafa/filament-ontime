<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\CompanyPolicy;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MasterDataApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Company $company;

    protected CompanyLocation $location;

    protected CompanyPolicy $policy;

    protected Division $division;

    protected JobLevel $jobLevel;

    protected JobTitle $jobTitle;

    protected Employee $employee;

    protected Holiday $holiday;

    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(RoleSeeder::class);

        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);

        $this->adminUser = User::factory()->create([
            'email' => 'masteradmin@ontime.com',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole($superadminRole);

        $this->company = Company::create([
            'name' => 'PT Utama Test',
            'code' => 'UTAMA',
            'is_active' => true,
        ]);

        $this->location = CompanyLocation::create([
            'company_id' => $this->company->id,
            'name' => 'Kantor Pusat',
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'radius_meters' => 150,
            'is_active' => true,
        ]);

        $this->policy = CompanyPolicy::create([
            'company_id' => $this->company->id,
            'late_tolerance_minutes' => 15,
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
        ]);

        $this->division = Division::create([
            'company_id' => $this->company->id,
            'name' => 'Divisi Teknologi',
            'code' => 'TECH',
            'is_active' => true,
        ]);

        $this->jobLevel = JobLevel::firstOrCreate([
            'name' => 'Lead',
            'level_order' => 5,
        ]);

        $this->jobTitle = JobTitle::firstOrCreate([
            'name' => 'Lead Architect',
            'division_id' => $this->division->id,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->adminUser->id,
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'job_level_id' => $this->jobLevel->id,
            'job_title_id' => $this->jobTitle->id,
            'nip' => 'ADM.001',
            'full_name' => 'Admin Master Test',
            'join_date' => now()->toDateString(),
            'status' => 'permanent',
        ]);

        $this->holiday = Holiday::create([
            'name' => 'Hari Kemerdekaan',
            'date' => '2026-08-17',
            'is_national' => true,
        ]);

        $this->attendance = Attendance::create([
            'employee_id' => $this->employee->id,
            'date' => now()->toDateString(),
            'check_in' => '08:00:00',
            'check_out' => '17:00:00',
            'status' => 'on_time',
        ]);
    }

    public function test_can_fetch_all_master_data_lists(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson('/api/v1/master/companies')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/master/company-locations')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/master/company-policies')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/master/divisions')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/master/job-titles')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/master/job-levels')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/master/employees')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/master/holidays')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/master/users')->assertStatus(200)->assertJsonStructure(['data']);
        $this->getJson('/api/v1/attendances')->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_can_fetch_master_data_details(): void
    {
        Sanctum::actingAs($this->adminUser);

        $this->getJson("/api/v1/master/companies/{$this->company->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'PT Utama Test');

        $this->getJson("/api/v1/master/company-locations/{$this->location->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Kantor Pusat');

        $this->getJson("/api/v1/master/company-policies/{$this->policy->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.late_tolerance_minutes', 15);

        $this->getJson("/api/v1/master/divisions/{$this->division->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Divisi Teknologi');

        $this->getJson("/api/v1/master/employees/{$this->employee->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.full_name', 'Admin Master Test');

        $this->getJson("/api/v1/master/holidays/{$this->holiday->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Hari Kemerdekaan');

        $this->getJson("/api/v1/attendances/{$this->attendance->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'on_time');
    }
}
