<?php

namespace Tests\Feature;

use App\Filament\Resources\FreelanceResource\Pages\CreateFreelancer;
use App\Filament\Resources\FreelanceResource\Pages\EditFreelancer;
use App\Filament\Resources\FreelanceResource\Pages\ListFreelancers;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Freelancer;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FreelanceResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected Company $company;

    protected Division $division;

    protected Employee $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Superadmin']);

        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('Superadmin');

        $this->company = Company::create([
            'code' => 'COMP1',
            'name' => 'PT Digital Solusi',
            'is_active' => true,
        ]);

        $this->division = Division::create([
            'company_id' => $this->company->id,
            'name' => 'Product & Design',
            'code' => 'DES',
            'is_active' => true,
        ]);

        $jobLevel = JobLevel::create(['name' => 'Manager', 'level_order' => 1]);
        $jobTitle = JobTitle::create(['name' => 'Product Lead']);

        $this->supervisor = Employee::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'EMP-002',
            'full_name' => 'Product Manager',
            'status' => 'active',
        ]);
    }

    public function test_can_render_freelance_list_page(): void
    {
        $this->actingAs($this->superadmin);

        $freelancer = Freelancer::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'supervisor_id' => $this->supervisor->id,
            'freelancer_number' => 'FL-001',
            'full_name' => 'Rina Freelancer',
            'institution' => 'UI/UX Designer Specialist',
            'status' => 'active',
        ]);

        Livewire::test(ListFreelancers::class)
            ->assertCanSeeTableRecords([$freelancer]);
    }

    public function test_can_create_freelancer(): void
    {
        $this->actingAs($this->superadmin);

        Livewire::test(CreateFreelancer::class)
            ->fillForm([
                'freelancer_number' => 'FL-002',
                'full_name' => 'Dewi Freelance',
                'institution' => 'Frontend Developer Specialist',
                'company_id' => $this->company->id,
                'division_id' => $this->division->id,
                'supervisor_id' => $this->supervisor->id,
                'email' => 'dewi@example.com',
                'phone' => '08987654321',
                'start_date' => '2026-08-01',
                'end_date' => '2026-12-31',
                'status' => 'active',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('freelancers', [
            'freelancer_number' => 'FL-002',
            'full_name' => 'Dewi Freelance',
            'institution' => 'Frontend Developer Specialist',
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'supervisor_id' => $this->supervisor->id,
            'email' => 'dewi@example.com',
            'status' => 'active',
        ]);
    }

    public function test_can_edit_freelancer(): void
    {
        $this->actingAs($this->superadmin);

        $freelancer = Freelancer::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'supervisor_id' => $this->supervisor->id,
            'freelancer_number' => 'FL-003',
            'full_name' => 'Doni Freelance',
            'institution' => 'Copywriter Agent',
            'status' => 'active',
        ]);

        Livewire::test(EditFreelancer::class, ['record' => $freelancer->id])
            ->fillForm([
                'status' => 'completed',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('freelancers', [
            'id' => $freelancer->id,
            'status' => 'completed',
        ]);
    }

    public function test_validates_unique_freelancer_number(): void
    {
        $this->actingAs($this->superadmin);

        Freelancer::create([
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'freelancer_number' => 'FL-EXISTING',
            'full_name' => 'Existing Freelancer',
            'status' => 'active',
        ]);

        Livewire::test(CreateFreelancer::class)
            ->fillForm([
                'freelancer_number' => 'FL-EXISTING',
                'full_name' => 'Duplicate Freelancer',
                'company_id' => $this->company->id,
                'division_id' => $this->division->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['freelancer_number' => 'unique']);
    }
}
