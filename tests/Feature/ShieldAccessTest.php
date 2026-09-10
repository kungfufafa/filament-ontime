<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShieldAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'Superadmin']);
        Role::create(['name' => 'Approver']);
        Role::create(['name' => 'Employee']);
    }

    public function test_non_superadmin_cannot_access_filament_shield_roles(): void
    {
        $approverUser = User::factory()->create();
        $approverUser->assignRole('Approver');

        $employeeUser = User::factory()->create();
        $employeeUser->assignRole('Employee');

        $policy = new RolePolicy;

        $this->assertFalse($policy->viewAny($approverUser));
        $this->assertFalse($policy->viewAny($employeeUser));
    }

    public function test_superadmin_can_access_filament_shield_roles(): void
    {
        $superadminUser = User::factory()->create();
        $superadminUser->assignRole('Superadmin');

        $policy = new RolePolicy;

        $this->assertTrue($policy->viewAny($superadminUser));
    }

    public function test_only_superadmin_can_access_user_resource(): void
    {
        $superadminUser = User::factory()->create();
        $superadminUser->assignRole('Superadmin');

        $approverUser = User::factory()->create();
        $approverUser->assignRole('Approver');

        $this->actingAs($superadminUser);
        $this->assertTrue(UserResource::canViewAny());

        $this->actingAs($approverUser);
        $this->assertFalse(UserResource::canViewAny());
    }
}
