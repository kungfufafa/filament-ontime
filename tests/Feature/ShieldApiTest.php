<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ShieldApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected User $employeeUser;

    protected Employee $employee;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(RoleSeeder::class);

        $this->company = Company::create([
            'name' => 'PT Shield API',
            'code' => 'SHIELDAPI',
            'is_active' => true,
        ]);

        $division = Division::create([
            'company_id' => $this->company->id,
            'name' => 'Divisi Shield',
            'code' => 'SHD',
            'is_active' => true,
        ]);

        $jobLevel = JobLevel::firstOrCreate(['name' => 'Staff', 'level_order' => 1]);
        $jobTitle = JobTitle::firstOrCreate(['name' => 'Staff Shield']);

        $this->superadmin = User::factory()->create([
            'name' => 'Superadmin Test',
            'email' => 'superadmin.shield@ontime.com',
        ]);
        $this->superadmin->assignRole('Superadmin');

        $this->employeeUser = User::factory()->create([
            'name' => 'Employee Shield',
            'email' => 'employee.shield@ontime.com',
        ]);
        $this->employeeUser->assignRole('Employee');

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'company_id' => $this->company->id,
            'division_id' => $division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'EMP.SHD.001',
            'full_name' => 'Employee Shield',
            'join_date' => now()->toDateString(),
            'status' => 'permanent',
        ]);
    }

    public function test_auth_me_returns_roles_and_shield_permissions(): void
    {
        Sanctum::actingAs($this->employeeUser);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.roles.0', 'Employee')
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'roles',
                    'permissions',
                ],
            ]);

        $permissions = $response->json('user.permissions');
        $this->assertContains('View:AbsenHariIni', $permissions);
    }

    public function test_superadmin_can_access_shield_roles_and_permissions_endpoints(): void
    {
        Sanctum::actingAs($this->superadmin);

        $rolesResponse = $this->getJson('/api/v1/shield/roles');
        $rolesResponse->assertStatus(200)
            ->assertJsonStructure(['roles' => [['id', 'name', 'permissions']]]);

        $permissionsResponse = $this->getJson('/api/v1/shield/permissions');
        $permissionsResponse->assertStatus(200)
            ->assertJsonStructure(['permissions' => [['id', 'name']]]);
    }

    public function test_regular_employee_forbidden_from_shield_roles_endpoint(): void
    {
        Sanctum::actingAs($this->employeeUser);

        $response = $this->getJson('/api/v1/shield/roles');
        $response->assertStatus(403);
    }

    public function test_master_data_apis_access_control(): void
    {
        // Employee has no ViewAny:Company permission
        Sanctum::actingAs($this->employeeUser);
        $this->getJson('/api/v1/master/companies')->assertStatus(403);

        // Superadmin has permission via Gate::before
        Sanctum::actingAs($this->superadmin);
        $this->getJson('/api/v1/master/companies')->assertStatus(200);
        $this->getJson('/api/v1/master/divisions')->assertStatus(200);
        $this->getJson('/api/v1/master/employees')->assertStatus(200);
    }

    public function test_laporan_absensi_api(): void
    {
        Sanctum::actingAs($this->employeeUser);

        $laporanResponse = $this->getJson('/api/v1/laporan-absensi');
        $laporanResponse->assertStatus(200)
            ->assertJsonStructure(['start_date', 'end_date', 'data']);
    }
}
