<?php

namespace Tests\Feature;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_policy_is_automatically_created_when_company_is_created(): void
    {
        $company = Company::create([
            'name' => 'PT Test Company',
            'code' => 'PTTC',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('company_policies', [
            'company_id' => $company->id,
            'late_tolerance_minutes' => 15,
            'require_photo' => 0,
            'require_gps' => 0,
            'annual_leave_quota' => 12,
            'default_approval_stages' => 1,
        ]);
    }

    public function test_company_policy_can_be_updated(): void
    {
        $company = Company::create([
            'name' => 'PT Test Company',
            'code' => 'PTTC',
            'is_active' => true,
        ]);

        $company->policy()->update([
            'late_tolerance_minutes' => 30,
            'require_photo' => true,
            'require_gps' => true,
            'geofence_radius_meters' => 200,
            'annual_leave_quota' => 14,
        ]);

        $this->assertDatabaseHas('company_policies', [
            'company_id' => $company->id,
            'late_tolerance_minutes' => 30,
            'require_photo' => 1,
            'require_gps' => 1,
            'geofence_radius_meters' => 200,
            'annual_leave_quota' => 14,
        ]);
    }
}
