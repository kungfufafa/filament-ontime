<?php

namespace Tests\Feature;

use App\Filament\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use App\Filament\Resources\OvertimeRequests\OvertimeRequestResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\AttendanceCorrection;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShieldAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Superadmin']);
        Role::create(['name' => 'Approver']);
        Role::create(['name' => 'Employee']);
    }

    public function test_non_superadmin_cannot_access_filament_shield_roles(): void
    {
        $approverUser = User::factory()->create();
        $approverUser->assignRole('Approver');

        $employeeUser = User::factory()->create();
        $employeeUser->assignRole('Employee');

        $policy = new RolePolicy;

        $this->assertFalse($policy->viewAny($approverUser));
        $this->assertFalse($policy->viewAny($employeeUser));
    }

    public function test_superadmin_can_access_filament_shield_roles(): void
    {
        $superadminUser = User::factory()->create();
        $superadminUser->assignRole('Superadmin');

        $policy = new RolePolicy;

        $this->assertTrue($policy->viewAny($superadminUser));
    }

    public function test_only_superadmin_can_access_user_resource(): void
    {
        $superadminUser = User::factory()->create();
        $superadminUser->assignRole('Superadmin');

        $approverUser = User::factory()->create();
        $approverUser->assignRole('Approver');

        $this->actingAs($superadminUser);
        $this->assertTrue(UserResource::canViewAny());

        $this->actingAs($approverUser);
        $this->assertFalse(UserResource::canViewAny());
    }

    public function test_only_superadmin_can_edit_or_delete_requests(): void
    {
        $superadminUser = User::factory()->create();
        $superadminUser->assignRole('Superadmin');

        $approverUser = User::factory()->create();
        $approverUser->assignRole('Approver');

        $resources = [
            [LeaveRequestResource::class, new LeaveRequest],
            [OvertimeRequestResource::class, new OvertimeRequest],
            [AttendanceCorrectionResource::class, new AttendanceCorrection],
        ];

        $this->actingAs($approverUser);

        foreach ($resources as [$resource, $request]) {
            $this->assertFalse($resource::canEdit($request));
            $this->assertFalse($resource::canDelete($request));
            $this->assertFalse($resource::canDeleteAny());
        }

        $this->actingAs($superadminUser);

        foreach ($resources as [$resource, $request]) {
            $this->assertTrue($resource::canEdit($request));
            $this->assertTrue($resource::canDelete($request));
            $this->assertTrue($resource::canDeleteAny());
        }
    }

    public function test_only_employee_can_track_their_own_approval_progress(): void
    {
        $company = Company::create(['name' => 'PT Test', 'code' => 'PTT', 'is_active' => true]);
        $division = Division::create(['company_id' => $company->id, 'name' => 'HR', 'code' => 'HR', 'is_active' => true]);
        $jobLevel = JobLevel::create(['name' => 'Staff', 'level_order' => 1]);
        $jobTitle = JobTitle::create(['name' => 'Staff HR']);

        $employeeUser = User::factory()->create();
        $employeeUser->assignRole('Employee');
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'EMP-001',
            'full_name' => 'Test Employee',
            'join_date' => today(),
            'status' => 'permanent',
        ]);

        $anotherEmployeeUser = User::factory()->create();
        $anotherEmployeeUser->assignRole('Employee');

        $approverUser = User::factory()->create();
        $approverUser->assignRole('Approver');

        $this->assertTrue($employeeUser->canTrackApprovalProgressFor($employee));
        $this->assertFalse($anotherEmployeeUser->canTrackApprovalProgressFor($employee));
        $this->assertFalse($approverUser->canTrackApprovalProgressFor($employee));
    }
}
