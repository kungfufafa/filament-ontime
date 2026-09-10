<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebhookReceiverTest extends TestCase
{
    use RefreshDatabase;

    public function test_processes_valid_webhook_payload()
    {
        config(['services.core.webhook_secret' => 'secret123']);

        DB::table('companies')->insert(['id' => 1, 'name' => 'Company', 'code' => 'C1']);
        DB::table('job_levels')->insert(['id' => 1, 'name' => 'Level 1']);
        DB::table('divisions')->insert(['id' => 1, 'name' => 'Div 1', 'company_id' => 1]);
        DB::table('job_titles')->insert(['id' => 1, 'name' => 'Title 1', 'division_id' => 1]);
        DB::table('employees')->insert([
            'id' => 99,
            'full_name' => 'Old Name',
            'nip' => 'EMP99',
            'company_id' => 1,
            'job_level_id' => 1,
            'job_title_id' => 1,
            'division_id' => 1,
            'status' => 'active',
            'email' => 'old@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'action' => 'updated',
            'employee' => [
                'id' => 99,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '0812345',
                'status' => 'active',
            ],
        ];

        $signature = hash_hmac('sha256', json_encode($payload), 'secret123');

        $response = $this->postJson('/api/webhooks/employees', $payload, [
            'X-Signature' => $signature,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('employees', [
            'id' => 99,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_rejects_invalid_signature()
    {
        config(['services.core.webhook_secret' => 'secret123']);

        $payload = [
            'action' => 'created',
            'employee' => [
                'id' => 99,
                'name' => 'John Doe',
                'status' => 'active',
            ],
        ];

        $response = $this->postJson('/api/webhooks/employees', $payload, [
            'X-Signature' => 'wrong-signature',
        ]);

        $response->assertStatus(401);
    }
}
