<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendancePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_selfie_photo_base64(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $dummyBase64 = 'data:image/jpeg;base64,'.base64_encode('fake-image-content');

        $response = $this->actingAs($user)->postJson(route('upload.selfie'), [
            'photo_base64' => $dummyBase64,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $path = $response->json('path');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }
}
