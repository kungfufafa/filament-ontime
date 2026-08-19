<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\User;
use App\Services\GeofenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiLocationGeofenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_check_in_at_any_active_company_location(): void
    {
        $company = Company::create([
            'name' => 'PT Multi Cabang',
            'code' => 'MULTICAB',
            'is_active' => true,
        ]);

        $company->policy()->create([
            'require_gps' => true,
            'geofence_radius_meters' => 100,
        ]);

        // Branch 1: Monas Jakarta (-6.1753924, 106.8271528)
        $loc1 = CompanyLocation::create([
            'company_id' => $company->id,
            'name' => 'Cabang Jakarta Monas',
            'latitude' => -6.1753924,
            'longitude' => 106.8271528,
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        // Branch 2: Bandung (-6.9174639, 107.6191228)
        $loc2 = CompanyLocation::create([
            'company_id' => $company->id,
            'name' => 'Cabang Bandung Merdeka',
            'latitude' => -6.9174639,
            'longitude' => 107.6191228,
            'radius_meters' => 150,
            'is_active' => true,
        ]);

        // User at Branch 2 (Bandung)
        $result = GeofenceService::validateCompanyGeofence($company, -6.9174000, 107.6191000);

        $this->assertTrue($result['is_valid']);
        $this->assertEquals('Cabang Bandung Merdeka', $result['matched_location_name']);

        // User far away (Surabaya)
        $resultFar = GeofenceService::validateCompanyGeofence($company, -7.2574719, 112.7520883);

        $this->assertFalse($resultFar['is_valid']);
        $this->assertStringContainsString('di luar radius geofence kantor terdekat', $resultFar['message']);
    }

    public function test_user_can_check_in_at_hq_when_branches_exist(): void
    {
        $company = Company::create([
            'name' => 'PT HQ & Branch',
            'code' => 'HQBRANCH',
            'latitude' => -6.7091607,
            'longitude' => 108.5532822,
            'is_active' => true,
        ]);

        $company->policy()->create([
            'require_gps' => true,
            'geofence_radius_meters' => 100,
        ]);

        CompanyLocation::create([
            'company_id' => $company->id,
            'name' => 'Cabang Bandung Merdeka',
            'latitude' => -6.9174639,
            'longitude' => 107.6191228,
            'radius_meters' => 200,
            'is_active' => true,
        ]);

        // User near HQ (-6.7094761, 108.5531037) (~40m away)
        $result = GeofenceService::validateCompanyGeofence($company, -6.7094761, 108.5531037);

        $this->assertTrue($result['is_valid']);
        $this->assertEquals('PT HQ & Branch (Kantor Utama)', $result['matched_location_name']);
    }
}
