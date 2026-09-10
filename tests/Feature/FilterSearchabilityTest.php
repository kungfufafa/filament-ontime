<?php

namespace Tests\Feature;

use App\Filament\Pages\KalenderCuti;
use App\Filament\Resources\ApproverResource;
use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\CompanyLocationResource;
use App\Filament\Resources\DivisionResource;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use App\Filament\Resources\FreelanceResource;
use App\Filament\Resources\InternResource;
use App\Filament\Resources\JobTitleResource;
use App\Filament\Resources\ResignationResource;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterSearchabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function makeTable(): Table
    {
        $livewire = new ListEmployees;

        return Table::make($livewire);
    }

    public function test_employee_resource_filters_searchability(): void
    {
        $table = EmployeeResource::table($this->makeTable());
        $filters = collect($table->getFilters())->keyBy(fn ($f) => $f->getName());

        $companyFilter = $filters->get('company_id');
        $divisionFilter = $filters->get('division_id');
        $jobLevelFilter = $filters->get('job_level_id');
        $statusFilter = $filters->get('status');

        $this->assertNotNull($companyFilter);
        $this->assertNotNull($divisionFilter);
        $this->assertNotNull($jobLevelFilter);
        $this->assertNotNull($statusFilter);

        // Initially with 0 records in DB, count <= 5, so searchable is false
        $this->assertFalse($companyFilter->getFormField()->isSearchable());
        $this->assertFalse($divisionFilter->getFormField()->isSearchable());
        $this->assertFalse($jobLevelFilter->getFormField()->isSearchable());
        $this->assertFalse($statusFilter->getFormField()->isSearchable());

        // Create 6 companies (> 5)
        for ($i = 1; $i <= 6; $i++) {
            Company::create(['name' => "PT Test {$i}", 'code' => "TST{$i}", 'is_active' => true]);
        }

        // Now companyFilter field must evaluate to searchable
        $this->assertTrue($companyFilter->getFormField()->isSearchable());
        // Division and status remain false
        $this->assertFalse($divisionFilter->getFormField()->isSearchable());
        $this->assertFalse($statusFilter->getFormField()->isSearchable());
    }

    public function test_freelance_and_intern_resource_filters_searchability(): void
    {
        $freelanceTable = FreelanceResource::table($this->makeTable());
        $fFilters = collect($freelanceTable->getFilters())->keyBy(fn ($f) => $f->getName());

        $internTable = InternResource::table($this->makeTable());
        $iFilters = collect($internTable->getFilters())->keyBy(fn ($f) => $f->getName());

        $this->assertFalse($fFilters->get('supervisor_id')->getFormField()->isSearchable());
        $this->assertFalse($iFilters->get('mentor_id')->getFormField()->isSearchable());
        $this->assertFalse($fFilters->get('status')->getFormField()->isSearchable());
        $this->assertFalse($iFilters->get('status')->getFormField()->isSearchable());

        // Create 6 employees (> 5)
        $company = Company::create(['name' => 'PT Mentor', 'code' => 'MEN', 'is_active' => true]);
        $division = Division::create(['company_id' => $company->id, 'name' => 'IT', 'code' => 'IT', 'is_active' => true]);
        $jobLevel = JobLevel::create(['name' => 'Staff', 'level_order' => 1]);
        $jobTitle = JobTitle::create(['name' => 'Developer', 'division_id' => $division->id]);

        for ($i = 1; $i <= 6; $i++) {
            $user = User::factory()->create();
            Employee::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'division_id' => $division->id,
                'job_level_id' => $jobLevel->id,
                'job_title_id' => $jobTitle->id,
                'nip' => "NIP-{$i}",
                'full_name' => "Employee {$i}",
                'status' => 'active',
            ]);
        }

        $this->assertTrue($fFilters->get('supervisor_id')->getFormField()->isSearchable());
        $this->assertTrue($iFilters->get('mentor_id')->getFormField()->isSearchable());
    }

    public function test_attendance_resource_filters_searchability(): void
    {
        $table = AttendanceResource::table($this->makeTable());
        $filters = collect($table->getFilters())->keyBy(fn ($f) => $f->getName());

        $workerTypeFilter = $filters->get('worker_type');
        $statusFilter = $filters->get('status');
        $companyFilter = $filters->get('company');

        $this->assertNotNull($workerTypeFilter);
        $this->assertNotNull($statusFilter);
        $this->assertNotNull($companyFilter);

        // worker_type has 3 options (<= 5) -> not searchable
        $this->assertFalse($workerTypeFilter->getFormField()->isSearchable());

        // status has AttendanceStatus with 8 options (> 5) -> searchable!
        $this->assertTrue($statusFilter->getFormField()->isSearchable());

        // Initially with 0 companies -> not searchable
        $this->assertFalse($companyFilter->getFormField()->isSearchable());

        // Create 6 companies (> 5)
        for ($i = 1; $i <= 6; $i++) {
            Company::create(['name' => "PT Att {$i}", 'code' => "ATT{$i}", 'is_active' => true]);
        }

        $this->assertTrue($companyFilter->getFormField()->isSearchable());
    }

    public function test_other_resources_filters_searchability(): void
    {
        // ApproverResource
        $approverTable = ApproverResource::table($this->makeTable());
        $aFilters = collect($approverTable->getFilters())->keyBy(fn ($f) => $f->getName());
        $this->assertNotNull($aFilters->get('company_id'));
        $this->assertNotNull($aFilters->get('division_id'));

        // CompanyLocationResource
        $locTable = CompanyLocationResource::table($this->makeTable());
        $locFilters = collect($locTable->getFilters())->keyBy(fn ($f) => $f->getName());
        $this->assertNotNull($locFilters->get('company_id'));

        // DivisionResource
        $divTable = DivisionResource::table($this->makeTable());
        $divFilters = collect($divTable->getFilters())->keyBy(fn ($f) => $f->getName());
        $this->assertNotNull($divFilters->get('company_id'));

        // JobTitleResource
        $jtTable = JobTitleResource::table($this->makeTable());
        $jtFilters = collect($jtTable->getFilters())->keyBy(fn ($f) => $f->getName());
        $this->assertNotNull($jtFilters->get('division_id'));

        // ResignationResource
        $resTable = ResignationResource::table($this->makeTable());
        $resFilters = collect($resTable->getFilters())->keyBy(fn ($f) => $f->getName());
        $this->assertNotNull($resFilters->get('employee_id'));
        $this->assertNotNull($resFilters->get('status'));
        $this->assertFalse($resFilters->get('status')->getFormField()->isSearchable());
    }

    public function test_kalender_cuti_filters_searchability(): void
    {
        $page = new KalenderCuti;
        $schema = KalenderCuti::form(new Schema($page));
        $components = $schema->getComponents();

        // Get Section -> Grid -> Selects
        $grid = $components[0]->getChildComponents()[0];
        $fields = collect($grid->getChildComponents())->keyBy(fn ($c) => $c->getName());

        $monthSelect = $fields->get('selectedMonth');
        $yearSelect = $fields->get('selectedYear');
        $companySelect = $fields->get('selectedCompanyId');

        $this->assertNotNull($monthSelect);
        $this->assertNotNull($yearSelect);
        $this->assertNotNull($companySelect);

        // Month has 12 options (> 5) -> must be searchable
        $this->assertTrue($monthSelect->isSearchable());

        // Year has 3 options (<= 5) -> must NOT be searchable
        $this->assertFalse($yearSelect->isSearchable());

        // Initially 0 companies in DB -> not searchable
        $this->assertFalse($companySelect->isSearchable());

        // Create 6 active companies (> 5)
        for ($i = 1; $i <= 6; $i++) {
            Company::create(['name' => "PT Cal {$i}", 'code' => "CAL{$i}", 'is_active' => true]);
        }

        // Re-evaluate form schema
        $freshSchema = KalenderCuti::form(new Schema($page));
        $freshGrid = $freshSchema->getComponents()[0]->getChildComponents()[0];
        $freshFields = collect($freshGrid->getChildComponents())->keyBy(fn ($c) => $c->getName());
        $this->assertTrue($freshFields->get('selectedCompanyId')->isSearchable());
    }
}
