<?php

namespace Tests\Feature;

use App\Filament\Pages\AbsenHariIni;
use App\Filament\Pages\KalenderCuti;
use App\Filament\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use App\Filament\Resources\OvertimeRequests\OvertimeRequestResource;
use App\Filament\Resources\ResignationResource;
use App\Models\ApprovalFlow;
use App\Models\Company;
use App\Models\Division;
use App\Models\Intern;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\ApprovalFlowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_intern_can_only_access_required_pages_and_resources(): void
    {
        $company = Company::create([
            'name' => 'PT Test',
            'code' => 'TEST',
            'is_active' => true,
        ]);

        $division = Division::create([
            'company_id' => $company->id,
            'name' => 'IT Support',
        ]);

        $user = User::factory()->create();

        Intern::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'nis' => 'INT-001',
            'full_name' => 'Intern Test',
            'institution' => 'University Test',
            'email' => $user->email,
            'phone' => '628123456789',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'status' => 'active',
        ]);

        $this->actingAs($user);

        // Allowed for Interns
        $this->assertTrue(AbsenHariIni::canAccess());
        $this->assertTrue(AttendanceCorrectionResource::canCreate());
        $this->assertTrue(AttendanceCorrectionResource::shouldRegisterNavigation());
        $this->assertTrue(AttendanceCorrectionResource::canViewAny());

        // Interns CAN now create leave and overtime requests
        $this->assertTrue(LeaveRequestResource::shouldRegisterNavigation());
        $this->assertTrue(LeaveRequestResource::canViewAny());
        $this->assertTrue(LeaveRequestResource::canCreate());

        $this->assertTrue(OvertimeRequestResource::shouldRegisterNavigation());
        $this->assertTrue(OvertimeRequestResource::canViewAny());
        $this->assertTrue(OvertimeRequestResource::canCreate());

        // Still hidden for Interns
        $this->assertFalse(ResignationResource::shouldRegisterNavigation());
        $this->assertFalse(ResignationResource::canViewAny());

        $this->assertFalse(KalenderCuti::canAccess());
    }

    public function test_intern_can_create_leave_request(): void
    {
        $company = Company::create([
            'name' => 'PT Test Company',
            'code' => 'TEST',
            'is_active' => true,
        ]);

        $division = Division::create([
            'company_id' => $company->id,
            'name' => 'IT Support',
        ]);

        ApprovalFlow::create([
            'company_id' => $company->id,
            'request_type' => 'leave',
            'step_number' => 1,
            'step_order' => 1,
            'name' => 'Direct Mentor',
            'approver_type' => 'role',
            'approver_role' => 'Approver',
        ]);

        $user = User::factory()->create();

        $intern = Intern::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'nis' => 'INT-002',
            'full_name' => 'Intern Permisi',
            'institution' => 'University Test',
            'email' => $user->email,
            'phone' => '628123456789',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $leaveRequest = LeaveRequest::create([
            'intern_id' => $intern->id,
            'leave_type' => 'permission',
            'start_date' => today(),
            'end_date' => today(),
            'days_count' => 1,
            'reason' => 'Izin keperluan kuliah',
            'status' => 'pending',
        ]);

        $service = new ApprovalFlowService;
        $service->generateSteps($leaveRequest, 'leave');

        $this->assertDatabaseHas('leave_requests', [
            'intern_id' => $intern->id,
            'leave_type' => 'permission',
            'reason' => 'Izin keperluan kuliah',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('approval_request_steps', [
            'approvable_type' => $leaveRequest->getMorphClass(),
            'approvable_id' => $leaveRequest->id,
            'step_name' => 'Direct Mentor',
        ]);
    }
}
