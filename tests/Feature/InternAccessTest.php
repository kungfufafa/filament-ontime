<?php

namespace Tests\Feature;

use App\Filament\Pages\AbsenHariIni;
use App\Filament\Pages\KalenderCuti;
use App\Filament\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use App\Filament\Resources\OvertimeRequests\OvertimeRequestResource;
use App\Filament\Resources\ResignationResource;
use App\Models\Company;
use App\Models\Division;
use App\Models\Intern;
use App\Models\User;
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

        // Interns CAN now see leave and overtime resources (design change: all worker types supported)
        // but CANNOT create because they need 'Employee' role — user factory creates user without role by default
        $this->assertTrue(LeaveRequestResource::shouldRegisterNavigation());
        $this->assertTrue(LeaveRequestResource::canViewAny());
        $this->assertFalse(LeaveRequestResource::canCreate()); // requires 'Employee' role

        $this->assertTrue(OvertimeRequestResource::shouldRegisterNavigation());
        $this->assertTrue(OvertimeRequestResource::canViewAny());
        $this->assertFalse(OvertimeRequestResource::canCreate()); // requires 'Employee' role

        // Still hidden for Interns
        $this->assertFalse(ResignationResource::shouldRegisterNavigation());
        $this->assertFalse(ResignationResource::canViewAny());

        $this->assertFalse(KalenderCuti::canAccess());
    }
}
