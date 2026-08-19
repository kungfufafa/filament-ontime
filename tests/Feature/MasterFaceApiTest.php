<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\JobTitle;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MasterFaceApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->seed(RoleSeeder::class);

        $company = Company::create([
            'name' => 'PT Master Face Test',
            'code' => 'PTMFT',
            'is_active' => true,
        ]);

        $division = Division::create([
            'company_id' => $company->id,
            'name' => 'Divisi IT',
            'code' => 'DIVIT',
            'is_active' => true,
        ]);

        $jobLevel = JobLevel::firstOrCreate(['name' => 'Staff', 'level_order' => 1]);
        $jobTitle = JobTitle::firstOrCreate(['name' => 'Programmer']);

        $this->user = User::factory()->create([
            'email' => 'masterface@example.com',
        ]);
        $this->user->assignRole('Employee');

        $this->employee = Employee::create([
            'user_id' => $this->user->id,
            'company_id' => $company->id,
            'division_id' => $division->id,
            'job_level_id' => $jobLevel->id,
            'job_title_id' => $jobTitle->id,
            'full_name' => 'Master Face User',
            'nip' => 'MF-1001',
            'join_date' => now()->toDateString(),
            'status' => 'permanent',
        ]);
    }

    public function test_user_can_upload_master_face_photo_via_api(): void
    {
        Storage::fake(config('filesystems.default'));
        Sanctum::actingAs($this->user);

        $file = UploadedFile::fake()->image('master_face.jpg', 300, 300);

        $response = $this->postJson('/api/v1/attendance/master-face', [
            'photo' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'master_face_photo',
                    'master_face_verified_at',
                ],
            ]);

        $this->employee->refresh();
        $this->assertNotNull($this->employee->master_face_photo);
        $this->assertNotNull($this->employee->master_face_verified_at);
        Storage::disk(config('filesystems.default'))->assertExists($this->employee->master_face_photo);
    }

    public function test_master_face_upload_validation_fails_without_photo(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/attendance/master-face', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }
}
