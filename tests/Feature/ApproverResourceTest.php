<?php

namespace Tests\Feature;

use App\Models\Approver;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApproverResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Approver']);
        Role::create(['name' => 'Superadmin']);
    }

    public function test_can_create_approver_mapping_for_user_with_approver_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Approver');

        $company = Company::create(['code' => 'MSI', 'name' => 'PT MSI', 'is_active' => true]);
        $division = Division::create(['company_id' => $company->id, 'name' => 'IT Department', 'code' => 'IT', 'is_active' => true]);

        $jobLevel = JobLevel::create(['name' => 'Staff', 'level_number' => 1]);
        $jobTitle = JobTitle::create(['name' => 'Staff IT']);

        $employee = Employee::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'EMP001',
            'full_name' => 'John Approver',
            'join_date' => now(),
            'status' => 'permanent',
        ]);

        $approver = Approver::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => null,
            'level' => 1,
        ]);

        $this->assertDatabaseHas('approvers', [
            'id' => $approver->id,
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => null,
            'level' => 1,
        ]);
    }

    public function test_cannot_create_approver_for_user_without_approver_role(): void
    {
        $user = User::factory()->create(); // No role
        $company = Company::create(['code' => 'MSI', 'name' => 'PT MSI', 'is_active' => true]);

        $this->expectException(ValidationException::class);

        Approver::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => null,
            'level' => 1,
        ]);
    }

    public function test_cannot_create_duplicate_approver_mapping(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Approver');
        $company = Company::create(['code' => 'MSI', 'name' => 'PT MSI', 'is_active' => true]);

        Approver::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => null,
            'level' => 1,
        ]);

        $this->expectException(ValidationException::class);

        Approver::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => null,
            'level' => 1,
        ]);
    }
}
