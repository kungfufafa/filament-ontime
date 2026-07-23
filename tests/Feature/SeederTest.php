<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_default_roles_and_superadmin(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Verify Roles
        $this->assertTrue(Role::where('name', 'Superadmin')->exists());
        $this->assertTrue(Role::where('name', 'BOD')->exists());
        $this->assertTrue(Role::where('name', 'Approver')->exists());
        $this->assertTrue(Role::where('name', 'Employee')->exists());

        // Verify Superadmin User
        $this->assertDatabaseHas('users', ['email' => 'admin@ontime.com']);
        $admin = User::where('email', 'admin@ontime.com')->first();
        $this->assertTrue($admin->hasRole('Superadmin'));
    }
}
