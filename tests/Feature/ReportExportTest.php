<?php

namespace Tests\Feature;

use App\Exports\AttendanceReportExport;
use App\Filament\Pages\LaporanAbsensi;
use App\Models\Approver;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Intern;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Approver']);
        Role::create(['name' => 'Superadmin']);
    }

    private function createCompanyWithEmployee(string $name, string $code): array
    {
        $company = Company::create(['name' => $name, 'code' => $code, 'is_active' => true]);
        $division = Division::create(['company_id' => $company->id, 'name' => 'Divisi '.$code, 'code' => $code, 'is_active' => true]);
        $jobLevel = JobLevel::create(['name' => 'Staff', 'level_number' => 1]);
        $jobTitle = JobTitle::create(['name' => 'Staff '.$code]);

        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'NIP'.$code,
            'full_name' => 'Karyawan '.$name,
            'join_date' => now(),
            'status' => 'permanent',
        ]);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => 'on_time',
        ]);

        return [$company, $division, $employee, $attendance];
    }

    public function test_approver_cannot_view_or_export_data_from_unauthorized_company(): void
    {
        [$companyA, $divisionA, $employeeA, $attendanceA] = $this->createCompanyWithEmployee('PT Alpha', 'ALPHA');
        [$companyB, $divisionB, $employeeB, $attendanceB] = $this->createCompanyWithEmployee('PT Beta', 'BETA');

        // Approver User mapped ONLY to Company A
        $approverUser = User::factory()->create();
        $approverUser->assignRole('Approver');
        Approver::create([
            'user_id' => $approverUser->id,
            'company_id' => $companyA->id,
            'division_id' => null,
            'level' => 1,
        ]);

        $this->actingAs($approverUser);

        $page = new LaporanAbsensi;
        $page->filterData = [
            'date_from' => today()->subDays(1)->toDateString(),
            'date_to' => today()->addDays(1)->toDateString(),
        ];

        $reportRecords = $page->getReportDataProperty();
        $employeeIds = $reportRecords->pluck('employee_id')->toArray();

        $this->assertContains($employeeA->id, $employeeIds);
        $this->assertNotContains($employeeB->id, $employeeIds);
    }

    public function test_superadmin_can_view_and_export_all_company_attendance_data(): void
    {
        [$companyA, $divisionA, $employeeA, $attendanceA] = $this->createCompanyWithEmployee('PT Alpha', 'ALPHA');
        [$companyB, $divisionB, $employeeB, $attendanceB] = $this->createCompanyWithEmployee('PT Beta', 'BETA');

        $superadminUser = User::factory()->create();
        $superadminUser->assignRole('Superadmin');

        $this->actingAs($superadminUser);

        $page = new LaporanAbsensi;
        $page->filterData = [
            'date_from' => today()->subDays(1)->toDateString(),
            'date_to' => today()->addDays(1)->toDateString(),
        ];

        $reportRecords = $page->getReportDataProperty();
        $employeeIds = $reportRecords->pluck('employee_id')->toArray();

        $this->assertContains($employeeA->id, $employeeIds);
        $this->assertContains($employeeB->id, $employeeIds);
    }

    public function test_export_excel_returns_valid_download_response(): void
    {
        [$companyA, $divisionA, $employeeA, $attendanceA] = $this->createCompanyWithEmployee('PT Alpha', 'ALPHA');

        $superadminUser = User::factory()->create();
        $superadminUser->assignRole('Superadmin');
        $this->actingAs($superadminUser);

        $page = new LaporanAbsensi;
        $page->filterData = [
            'date_from' => today()->subDays(1)->toDateString(),
            'date_to' => today()->toDateString(),
        ];

        $response = $page->exportExcel();

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringContainsString('laporan-rekap-absensi.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_attendance_report_export_applies_correct_weekend_rules_for_employee_and_intern(): void
    {
        [$company, $division, $employee] = $this->createCompanyWithEmployee('PT Gamma', 'GAMMA');

        $internUser = User::factory()->create();
        $intern = Intern::create([
            'user_id' => $internUser->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'nis' => 'INT-001',
            'full_name' => 'Peserta Magang Gamma',
            'institution' => 'SMK 1',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        // Range containing Friday (workday), Saturday (weekend for intern, workday for employee), and Sunday (weekend for both)
        // 2026-09-04 is Friday, 2026-09-05 is Saturday, 2026-09-06 is Sunday
        $friday = Carbon::parse('2026-09-04');
        $saturday = Carbon::parse('2026-09-05');
        $sunday = Carbon::parse('2026-09-06');

        // Create Friday attendance for both
        $attEmployee = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $friday,
            'check_in' => $friday->copy()->setTime(8, 5, 0),
            'check_out' => $friday->copy()->setTime(17, 0, 0),
            'status' => 'on_time',
        ]);

        $attIntern = Attendance::create([
            'intern_id' => $intern->id,
            'date' => $friday,
            'check_in' => $friday->copy()->setTime(7, 55, 0),
            'check_out' => $friday->copy()->setTime(16, 30, 0),
            'status' => 'on_time',
        ]);

        $records = collect([$attEmployee, $attIntern]);

        $export = new AttendanceReportExport(
            records: $records,
            dateFrom: $friday,
            dateTo: $sunday,
            companyName: $company->name,
            divisionName: $division->name,
        );

        $view = $export->view();
        $data = $view->getData();

        $this->assertArrayHasKey('rows', $data);
        $this->assertArrayHasKey('months', $data);

        $rows = collect($data['rows'])->keyBy('nip');

        $empRow = $rows->get($employee->nip);
        $intRow = $rows->get($intern->nis);

        $this->assertNotNull($empRow);
        $this->assertNotNull($intRow);

        // Check Friday (both attended)
        $this->assertEquals('08:05', $empRow['days']['2026-09-04']['in']);
        $this->assertEquals('17:00', $empRow['days']['2026-09-04']['out']);
        $this->assertEquals('07:55', $intRow['days']['2026-09-04']['in']);
        $this->assertEquals('16:30', $intRow['days']['2026-09-04']['out']);

        // Check Saturday:
        // Employee has no attendance -> '-' (work day)
        // Intern has no attendance -> 'Libur' (auto weekend holiday for intern)
        $this->assertEquals('-', $empRow['days']['2026-09-05']['in']);
        $this->assertEquals('Libur', $intRow['days']['2026-09-05']['in']);

        // Check Sunday:
        // Both employee and intern have 'Libur' (auto weekend holiday for both)
        $this->assertEquals('Libur', $empRow['days']['2026-09-06']['in']);
        $this->assertEquals('Libur', $intRow['days']['2026-09-06']['in']);
    }

    public function test_laporan_absensi_can_filter_by_worker_type(): void
    {
        [$company, $division, $employee] = $this->createCompanyWithEmployee('PT Zeta', 'ZETA');

        $internUser = User::factory()->create();
        $intern = Intern::create([
            'user_id' => $internUser->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'nis' => 'INT-ZETA-1',
            'full_name' => 'Intern Zeta',
            'institution' => 'SMK Zeta',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        Attendance::create([
            'intern_id' => $intern->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => 'on_time',
        ]);

        $superadminUser = User::factory()->create();
        $superadminUser->assignRole('Superadmin');
        $this->actingAs($superadminUser);

        // 1. Filter specifically for Intern
        $page = new LaporanAbsensi;
        $page->filterData = [
            'date_from' => today()->toDateString(),
            'date_to' => today()->toDateString(),
            'worker_type' => 'intern',
        ];

        $internRecords = $page->getReportDataProperty();
        $this->assertTrue($internRecords->every(fn ($r) => $r->intern_id !== null && $r->employee_id === null));
        $this->assertEquals(1, $internRecords->count());

        // 2. Filter specifically for Employee
        $page->filterData['worker_type'] = 'employee';
        $employeeRecords = $page->getReportDataProperty();
        $this->assertTrue($employeeRecords->every(fn ($r) => $r->employee_id !== null && $r->intern_id === null));
        $this->assertEquals(1, $employeeRecords->count());

        // 3. Test Excel export view with worker_type
        $export = new AttendanceReportExport(
            records: $internRecords,
            dateFrom: today(),
            dateTo: today(),
            workerType: 'intern'
        );

        $view = $export->view();
        $rendered = $view->render();

        // Assert title includes BULANAN MAGANG and table headers don't have NIP / ID or Divisi columns
        $this->assertStringContainsString('REKAPITULASI ABSENSI BULANAN MAGANG', $rendered);
        $this->assertStringNotContainsString('>NIP / ID<', $rendered);
        $this->assertStringNotContainsString('>Divisi<', $rendered);
    }

    public function test_attendance_report_export_does_not_create_phantom_columns(): void
    {
        $export = new AttendanceReportExport(
            records: collect([]),
            dateFrom: Carbon::parse('2026-09-01'),
            dateTo: Carbon::parse('2026-09-07'),
        );

        $export->view();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $export->styles($sheet);

        $this->assertNotEquals('ZZ', $sheet->getHighestColumn());
    }

    public function test_select_is_searchable_only_when_options_exceed_five(): void
    {
        $selectShort = Select::make('short')
            ->options(['1' => 'A', '2' => 'B', '3' => 'C'])
            ->searchable(fn (Select $component): bool => count($component->getOptions()) > 5);

        $selectLong = Select::make('long')
            ->options([
                '1' => 'A', '2' => 'B', '3' => 'C',
                '4' => 'D', '5' => 'E', '6' => 'F',
            ])
            ->searchable(fn (Select $component): bool => count($component->getOptions()) > 5);

        $this->assertFalse($selectShort->isSearchable());
        $this->assertTrue($selectLong->isSearchable());

        $this->assertTrue(method_exists(SelectFilter::class, 'searchable'));
        $filter = SelectFilter::make('test_filter')
            ->options(['1' => 'A', '2' => 'B'])
            ->searchable(fn (SelectFilter $f): bool => count($f->getOptions()) > 5);
        $field = $filter->getFormField();
        $this->assertFalse($field->isSearchable());

        $filterLong = SelectFilter::make('test_filter_long')
            ->options(['1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D', '5' => 'E', '6' => 'F'])
            ->searchable(fn (SelectFilter $f): bool => count($f->getOptions()) > 5);
        $fieldLong = $filterLong->getFormField();
        $this->assertTrue($fieldLong->isSearchable());

        // Test relationship with model count condition
        $filterRel = SelectFilter::make('company_id')
            ->relationship('company', 'name')
            ->searchable(fn (): bool => Company::count() > 5)
            ->preload();
        $fieldRel = $filterRel->getFormField();
        $this->assertFalse($fieldRel->isSearchable()); // Since we have fewer than 5 companies
    }
}
