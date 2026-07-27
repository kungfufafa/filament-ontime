<?php

namespace Tests\Feature;

use App\Livewire\Auth\PhoneLogin;
use App\Models\User;
use App\Models\WhatsappOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PhoneLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_login_page_can_be_rendered(): void
    {
        config([
            'services.gateway_hub.url' => 'http://gateway-hub.test/send',
            'services.gateway_hub.enabled' => true,
        ]);
        $response = $this->get('/phone-login');
        $response->assertStatus(200);
    }

    public function test_phone_login_returns_404_when_gateway_url_is_empty(): void
    {
        config([
            'services.gateway_hub.url' => null,
            'services.gateway_hub.enabled' => false,
        ]);
        $response = $this->get('/phone-login');
        $response->assertStatus(404);
    }

    public function test_otp_can_be_requested_for_valid_user(): void
    {
        config([
            'services.gateway_hub.url' => 'http://gateway-hub.test/send',
            'services.gateway_hub.enabled' => true,
        ]);
        Http::fake([
            'http://gateway-hub.test/*' => Http::response(['status' => true, 'message' => 'Sent'], 200),
        ]);

        $user = User::factory()->create([
            'phone' => '085810117452',
            'is_active' => true,
        ]);

        Livewire::test(PhoneLogin::class)
            ->set('data.whatsapp_number', '085810117452')
            ->call('send')
            ->assertSet('awaitingOtp', true)
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('whatsapp_otps', [
            'phone' => '6285810117452',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'gateway-hub.test')
                && str_contains($request['message'], 'Kode OTP Login OnTime Anda adalah');
        });
    }

    public function test_invalid_phone_number_shows_error(): void
    {
        config([
            'services.gateway_hub.url' => 'http://gateway-hub.test/send',
            'services.gateway_hub.enabled' => true,
        ]);
        Http::fake();

        Livewire::test(PhoneLogin::class)
            ->fillForm([
                'whatsapp_number' => '089999999999',
            ])
            ->call('send')
            ->assertSet('awaitingOtp', false)
            ->assertHasFormErrors(['whatsapp_number']);

        Http::assertNothingSent();
    }

    public function test_user_can_login_with_valid_otp(): void
    {
        config([
            'services.gateway_hub.url' => 'http://gateway-hub.test/send',
            'services.gateway_hub.enabled' => true,
        ]);
        Http::fake([
            'http://gateway-hub.test/*' => Http::response(['status' => true], 200),
        ]);

        $user = User::factory()->create([
            'phone' => '085810117452',
            'is_active' => true,
        ]);

        $test = Livewire::test(PhoneLogin::class)
            ->fillForm([
                'whatsapp_number' => '085810117452',
            ])
            ->call('send')
            ->assertSet('awaitingOtp', true);

        $otpRecord = WhatsappOtp::where('phone', '6285810117452')->first();
        $this->assertNotNull($otpRecord);

        $test->fillForm([
            'whatsapp_number' => '085810117452',
            'otp' => $otpRecord->otp,
        ])
            ->call('verify')
            ->assertRedirect('/admin');

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_invalid_otp_fails_authentication(): void
    {
        config([
            'services.gateway_hub.url' => 'http://gateway-hub.test/send',
            'services.gateway_hub.enabled' => true,
        ]);
        Http::fake([
            'http://gateway-hub.test/*' => Http::response(['status' => true], 200),
        ]);

        $user = User::factory()->create([
            'phone' => '085810117452',
            'is_active' => true,
        ]);

        Livewire::test(PhoneLogin::class)
            ->fillForm([
                'whatsapp_number' => '085810117452',
            ])
            ->call('send')
            ->fillForm([
                'whatsapp_number' => '085810117452',
                'otp' => '999999',
            ])
            ->call('verify')
            ->assertHasFormErrors(['otp']);

        $this->assertFalse(Auth::check());
    }

    public function test_phone_number_is_automatically_normalized_in_database(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
        ]);

        $this->assertEquals('6281234567890', $user->phone);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '6281234567890',
        ]);
    }
}
