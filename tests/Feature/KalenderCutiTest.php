<?php

namespace Tests\Feature;

use App\Filament\Pages\KalenderCuti;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KalenderCutiTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Superadmin']);

        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('Superadmin');

        $company = Company::create([
            'code' => 'COMP-CAL',
            'name' => 'PT Kalender Test',
            'is_active' => true,
        ]);

        $division = Division::create([
            'company_id' => $company->id,
            'name' => 'HRD',
            'code' => 'HRD',
            'is_active' => true,
        ]);

        $jobLevel = JobLevel::create(['name' => 'Staff', 'level_order' => 1]);
        $jobTitle = JobTitle::create(['name' => 'HR Staff']);

        $user = User::factory()->create();

        $this->employee = Employee::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'EMP-CAL-01',
            'full_name' => 'Staff Kalender Cuti',
            'status' => 'active',
        ]);
    }

    public function test_can_render_kalender_cuti_page(): void
    {
        $this->actingAs($this->superadmin);

        LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type' => 'annual_leave',
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->startOfMonth()->addDays(2)->format('Y-m-d'),
            'days_count' => 3,
            'reason' => 'Liburan keluarga',
            'status' => 'approved',
        ]);

        Livewire::test(KalenderCuti::class)
            ->assertSuccessful()
            ->assertSee('Staff Kalender Cuti');
    }
}
