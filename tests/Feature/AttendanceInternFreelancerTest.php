<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\CompanyPolicy;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Freelancer;
use App\Models\Intern;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceInternFreelancerTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private Division $division;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Superadmin']);
        Role::create(['name' => 'Approver']);
        Role::create(['name' => 'BOD']);
        Role::create(['name' => 'Employee']);

        $this->company = Company::create([
            'name' => 'PT Test',
            'code' => 'PTT',
            'is_active' => true,
        ]);

        $this->division = Division::create([
            'company_id' => $this->company->id,
            'name' => 'IT',
            'code' => 'IT001',
            'is_active' => true,
        ]);

        // Configure company policy with work hours
        CompanyPolicy::where('company_id', $this->company->id)->update([
            'work_start_time' => '08:00:00',
            'late_tolerance_minutes' => 15,
        ]);
    }

    /**
     * Helper: Create a User linked to an Intern profile.
     */
    private function createInternUser(): array
    {
        $user = User::factory()->create();
        $user->assignRole('Employee');

        $intern = Intern::create([
            'user_id' => $user->id,
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'nis' => 'NIS'.rand(1000, 9999),
            'full_name' => 'Intern Test User',
            'institution' => 'Universitas Test',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        return compact('user', 'intern');
    }

    /**
     * Helper: Create a User linked to a Freelancer profile.
     */
    private function createFreelancerUser(): array
    {
        $employeeUser = User::factory()->create();
        $jobLevel = JobLevel::create(['name' => 'Staff', 'level_number' => 1]);
        $jobTitle = JobTitle::create(['name' => 'Developer']);

        $supervisorEmployee = Employee::create([
            'user_id' => $employeeUser->id,
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'nip' => 'EMP'.rand(100, 999),
            'full_name' => 'Supervisor',
            'join_date' => now(),
            'status' => 'active',
        ]);

        $user = User::factory()->create();
        $user->assignRole('Employee');

        $freelancer = Freelancer::create([
            'user_id' => $user->id,
            'company_id' => $this->company->id,
            'division_id' => $this->division->id,
            'supervisor_id' => $supervisorEmployee->id,
            'freelancer_number' => 'FRL'.rand(1000, 9999),
            'full_name' => 'Freelancer Test User',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'status' => 'active',
        ]);

        return compact('user', 'freelancer');
    }

    // ── Test 1: Intern can create an attendance record ───────────────────────

    public function test_intern_can_create_attendance_record(): void
    {
        ['user' => $user, 'intern' => $intern] = $this->createInternUser();

        $attendance = Attendance::create([
            'intern_id' => $intern->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $this->assertDatabaseHas('attendances', [
            'intern_id' => $intern->id,
            'employee_id' => null,
            'freelancer_id' => null,
        ]);

        $this->assertInstanceOf(AttendanceStatus::class, $attendance->fresh()->status);
        $this->assertEquals(AttendanceStatus::OnTime, $attendance->fresh()->status);
    }

    // ── Test 2: Freelancer can create an attendance record ───────────────────

    public function test_freelancer_can_create_attendance_record(): void
    {
        ['user' => $user, 'freelancer' => $freelancer] = $this->createFreelancerUser();

        $attendance = Attendance::create([
            'freelancer_id' => $freelancer->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $this->assertDatabaseHas('attendances', [
            'freelancer_id' => $freelancer->id,
            'employee_id' => null,
            'intern_id' => null,
        ]);
    }

    // ── Test 3: Intern cannot check-in twice on the same day ─────────────────

    public function test_intern_cannot_have_duplicate_attendance_on_same_day(): void
    {
        ['intern' => $intern] = $this->createInternUser();

        Attendance::create([
            'intern_id' => $intern->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $this->expectException(QueryException::class);

        Attendance::create([
            'intern_id' => $intern->id,
            'date' => today(),
            'check_in' => now()->setTime(9, 0, 0),
            'status' => AttendanceStatus::Late,
            'late_minutes' => 60,
        ]);
    }

    // ── Test 4: Freelancer cannot have duplicate attendance on same day ───────

    public function test_freelancer_cannot_have_duplicate_attendance_on_same_day(): void
    {
        ['freelancer' => $freelancer] = $this->createFreelancerUser();

        Attendance::create([
            'freelancer_id' => $freelancer->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $this->expectException(QueryException::class);

        Attendance::create([
            'freelancer_id' => $freelancer->id,
            'date' => today(),
            'check_in' => now()->setTime(9, 0, 0),
            'status' => AttendanceStatus::Late,
            'late_minutes' => 60,
        ]);
    }

    // ── Test 5: scopeByWorker returns correct intern attendance ──────────────

    public function test_scope_by_worker_returns_intern_attendance(): void
    {
        ['user' => $user, 'intern' => $intern] = $this->createInternUser();

        Attendance::create([
            'intern_id' => $intern->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        // Another intern that should NOT show up
        ['intern' => $otherIntern] = $this->createInternUser();
        Attendance::create([
            'intern_id' => $otherIntern->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $result = Attendance::byWorker($user)->today()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($intern->id, $result->first()->intern_id);
    }

    // ── Test 6: scopeByWorker returns correct freelancer attendance ──────────

    public function test_scope_by_worker_returns_freelancer_attendance(): void
    {
        ['user' => $user, 'freelancer' => $freelancer] = $this->createFreelancerUser();

        Attendance::create([
            'freelancer_id' => $freelancer->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $result = Attendance::byWorker($user)->today()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($freelancer->id, $result->first()->freelancer_id);
    }

    // ── Test 7: AttendanceStatus Late is cast correctly on intern record ──────

    public function test_late_status_is_cast_to_enum_for_intern(): void
    {
        ['intern' => $intern] = $this->createInternUser();

        $attendance = Attendance::create([
            'intern_id' => $intern->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 30, 0),
            'status' => AttendanceStatus::Late,
            'late_minutes' => 15,
        ]);

        $fresh = $attendance->fresh();
        $this->assertInstanceOf(AttendanceStatus::class, $fresh->status);
        $this->assertEquals(AttendanceStatus::Late, $fresh->status);
        $this->assertEquals('Terlambat', $fresh->status->getLabel());
        $this->assertEquals('warning', $fresh->status->getColor());
    }

    // ── Test 8: Check-out succeeds after check-in for intern ─────────────────

    public function test_intern_can_check_out_after_check_in(): void
    {
        ['intern' => $intern] = $this->createInternUser();

        $attendance = Attendance::create([
            'intern_id' => $intern->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $attendance->update([
            'check_out' => now()->setTime(17, 0, 0),
        ]);

        $this->assertNotNull($attendance->fresh()->check_out);
        $this->assertEquals('17:00', $attendance->fresh()->check_out->format('H:i'));
    }

    // ── Test 9: Attendance by intern and freelancer on same day are independent

    public function test_intern_and_freelancer_attendances_on_same_day_are_independent(): void
    {
        ['intern' => $intern] = $this->createInternUser();
        ['freelancer' => $freelancer] = $this->createFreelancerUser();

        Attendance::create([
            'intern_id' => $intern->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        Attendance::create([
            'freelancer_id' => $freelancer->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 5, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $this->assertDatabaseCount('attendances', 2);

        $internAttendance = Attendance::where('intern_id', $intern->id)->whereDate('date', today())->first();
        $freelancerAttendance = Attendance::where('freelancer_id', $freelancer->id)->whereDate('date', today())->first();

        $this->assertNotNull($internAttendance);
        $this->assertNotNull($freelancerAttendance);
        $this->assertNotEquals($internAttendance->id, $freelancerAttendance->id);
    }

    // ── Test 10: scopeByWorker returns empty for user with no profile ─────────

    public function test_scope_by_worker_returns_empty_for_user_with_no_profile(): void
    {
        $userWithNoProfile = User::factory()->create();

        // Create some attendances from other workers to ensure they're not returned
        ['intern' => $intern] = $this->createInternUser();
        Attendance::create([
            'intern_id' => $intern->id,
            'date' => today(),
            'check_in' => now()->setTime(8, 0, 0),
            'status' => AttendanceStatus::OnTime,
            'late_minutes' => 0,
        ]);

        $result = Attendance::byWorker($userWithNoProfile)->today()->get();

        $this->assertCount(0, $result);
    }
}
