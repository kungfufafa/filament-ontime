<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyPolicy;
use App\Services\FaceRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FaceRecognitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_policy_stores_face_recognition_settings(): void
    {
        $company = Company::create([
            'name' => 'PT Face Recognition Test',
            'code' => 'PTFRT',
            'is_active' => true,
        ]);

        $company->policy()->update([
            'require_face_recognition' => true,
            'face_match_threshold' => 75,
            'face_fail_action' => 'approval',
        ]);

        $this->assertDatabaseHas('company_policies', [
            'company_id' => $company->id,
            'require_face_recognition' => 1,
            'face_match_threshold' => 75,
            'face_fail_action' => 'approval',
        ]);
    }

    public function test_face_recognition_service_returns_error_when_master_photo_missing(): void
    {
        $service = app(FaceRecognitionService::class);
        $result = $service->verifyFace('some/selfie.jpg', null);

        $this->assertFalse($result['is_matched']);
        $this->assertEquals(0, $result['score']);
        $this->assertStringContainsString('Foto Master Wajah belum didaftarkan', $result['notes']);
    }

    public function test_face_recognition_service_verifies_identical_images(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('master_face.jpg', 100, 100);
        $path = $file->store('master-faces', 'public');

        $policy = new CompanyPolicy([
            'face_match_threshold' => 60,
        ]);

        $service = app(FaceRecognitionService::class);
        $result = $service->verifyFace($path, $path, $policy);

        $this->assertTrue($result['is_matched']);
        $this->assertGreaterThanOrEqual(60, $result['score']);
    }
}
