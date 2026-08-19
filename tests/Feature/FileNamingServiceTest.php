<?php

namespace Tests\Feature;

use App\Services\FileNamingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileNamingServiceTest extends TestCase
{
    public function test_generate_file_name_formats_correctly_with_uploaded_file(): void
    {
        $file = UploadedFile::fake()->create('surat_dokter.pdf', 100, 'application/pdf');

        $generatedName = FileNamingService::generateFileName('LEAVE', 'EMP001', $file);

        $this->assertStringStartsWith('LEAVE_EMP001_', $generatedName);
        $this->assertStringEndsWith('.pdf', $generatedName);
        $this->assertStringContainsString('_surat-dokter_', $generatedName);
    }

    public function test_generate_file_name_handles_null_identifier(): void
    {
        $file = UploadedFile::fake()->create('bukti.png', 100, 'image/png');

        $generatedName = FileNamingService::generateFileName('CORR', null, $file);

        $this->assertStringStartsWith('CORR_UNIDENTIFIED_', $generatedName);
        $this->assertStringEndsWith('.png', $generatedName);
    }

    public function test_store_uploaded_file_stores_with_structured_name(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('selfie.jpg', 200, 'image/jpeg');

        $path = FileNamingService::storeUploadedFile($file, 'attendance/photos', 'ATT_IN', 'EMP999', 'public');

        Storage::disk('public')->assertExists($path);
        $this->assertStringStartsWith('attendance/photos/ATT_IN_EMP999_', $path);
    }
}
