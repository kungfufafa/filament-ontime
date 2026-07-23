<?php

namespace Tests\Feature;

use App\Models\ApprovalFlow;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Approver']);
        Role::create(['name' => 'BOD']);
        Role::create(['name' => 'Superadmin']);
    }

    public function test_can_create_valid_approval_flow_steps(): void
    {
        $company = Company::create([
            'name' => 'PT Flow Test',
            'code' => 'PTFT',
            'is_active' => true,
        ]);

        ApprovalFlow::create([
            'company_id' => $company->id,
            'request_type' => 'leave',
            'step_number' => 1,
            'step_order' => 1,
            'name' => 'Direct Manager',
            'approver_type' => 'role',
            'approver_role' => 'Approver',
        ]);

        ApprovalFlow::create([
            'company_id' => $company->id,
            'request_type' => 'leave',
            'step_number' => 2,
            'step_order' => 2,
            'name' => 'BOD Review',
            'approver_type' => 'role',
            'approver_role' => 'BOD',
        ]);

        ApprovalFlow::create([
            'company_id' => $company->id,
            'request_type' => 'leave',
            'step_number' => 3,
            'step_order' => 3,
            'name' => 'Final Superadmin Approval',
            'approver_type' => 'role',
            'approver_role' => 'Superadmin',
        ]);

        $this->assertDatabaseHas('approval_flows', [
            'company_id' => $company->id,
            'request_type' => 'leave',
            'step_order' => 1,
            'name' => 'Direct Manager',
        ]);

        $this->assertDatabaseHas('approval_flows', [
            'company_id' => $company->id,
            'request_type' => 'leave',
            'step_order' => 3,
            'approver_role' => 'Superadmin',
        ]);
    }
}
