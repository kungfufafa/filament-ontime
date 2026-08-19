<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyLocation;
use Illuminate\Database\Seeder;

class CompanyLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Kurasi menyeluruh berdasarkan data riil internet & struktur produksi Complete Selular Group (Ocean Space).
     */
    public function run(): void
    {
        $companiesData = [
            // =========================================================================
            // 1. CS - CV Complete Selular (Main Retail Stores Chain & HQ)
            // =========================================================================
            'CS' => [
                'name' => 'CV Complete Selular',
                'address' => 'Jl. Tuparev No. 109F, Kertawinangun, Kec. Kedawung, Kab. Cirebon, Jawa Barat 45153',
                'latitude' => -6.7088900,
                'longitude' => 108.5348800,
                'radius_meters' => 100,
                'locations' => [
                    [
                        'name' => 'HQ Complete Selular Tuparev',
                        'address' => 'Jl. Tuparev No. 109F, Kertawinangun, Kedawung, Cirebon, Jawa Barat 45153',
                        'latitude' => -6.7088900,
                        'longitude' => 108.5348800,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Kantor Operasional Tentara Pelajar',
                        'address' => 'Jl. Tentara Pelajar No. 72B-C, Kejaksan, Kota Cirebon, Jawa Barat 45122',
                        'latitude' => -6.7161000,
                        'longitude' => 108.5574000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Tuparev',
                        'address' => 'Jl. Tuparev No. 109F, Kertawinangun, Kedawung, Cirebon 45153',
                        'latitude' => -6.7088900,
                        'longitude' => 108.5348800,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Perumnas',
                        'address' => 'Jl. Gunung Malabar I No. 282 (Depan SMAN 3), Harjamukti, Cirebon 45142',
                        'latitude' => -6.7455000,
                        'longitude' => 108.5582000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Surya Toserba',
                        'address' => 'Surya Toserba Lt. 1, Jl. Karanggetas No. 23, Pekalipan, Cirebon 45118',
                        'latitude' => -6.7198000,
                        'longitude' => 108.5632000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Ciledug',
                        'address' => 'Jl. Merdeka Barat No. 66, Ciledug Lor, Ciledug, Cirebon 45188',
                        'latitude' => -6.9032000,
                        'longitude' => 108.7461000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Sindang',
                        'address' => 'Cipeujeuh Wetan, Kec. Lemahabang, Kab. Cirebon 45183',
                        'latitude' => -6.8285000,
                        'longitude' => 108.6210000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Babakan',
                        'address' => 'Jl. Pangeran Surajaya No. 139 (Samping Pasar Babakan), Babakan, Cirebon 45191',
                        'latitude' => -6.8654000,
                        'longitude' => 108.7231000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Gebang',
                        'address' => 'Jl. Pangeran Sutajaya (Utara Pegadaian), Gebang Mekar, Gebang, Cirebon 45191',
                        'latitude' => -6.8321000,
                        'longitude' => 108.7185000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Pabuaran',
                        'address' => 'Jl. Letjend S. Parman, Pabuaran Wetan, Pabuaran, Cirebon 45187',
                        'latitude' => -6.8845000,
                        'longitude' => 108.7012000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Unboxing Store Megu Plered',
                        'address' => 'Jl. Fatahillah/Sumber Plered, Blok Randualas No. 70, Megu Cilik, Weru, Cirebon 45154',
                        'latitude' => -6.7121000,
                        'longitude' => 108.5089000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Oppo Store Tentara Pelajar',
                        'address' => 'Jl. Tentara Pelajar No. 72B-C, Kejaksan, Kota Cirebon 45122',
                        'latitude' => -6.7161000,
                        'longitude' => 108.5574000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Jatiwangi 1',
                        'address' => 'Jl. Raya Sutawangi No. 52 (Depan Koramil), Sutawangi, Jatiwangi, Majalengka 45454',
                        'latitude' => -6.7412000,
                        'longitude' => 108.2654000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Jatiwangi 2',
                        'address' => 'Jl. Raya Ciborelang No. 6, Ciborelang, Jatiwangi, Majalengka 45454',
                        'latitude' => -6.7485000,
                        'longitude' => 108.2712000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Patrol',
                        'address' => 'Jl. Raya Patrol No. 33, Patrol, Kab. Indramayu 45257',
                        'latitude' => -6.3054000,
                        'longitude' => 107.9942000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Global Selular Jatibarang',
                        'address' => 'Jl. Siliwangi No. 113, Jatibarang, Kab. Indramayu 45273',
                        'latitude' => -6.4712000,
                        'longitude' => 108.3054000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Selular Plus Jatibarang',
                        'address' => 'Jl. Mayor Dasuki No. 70, Jatibarang, Kab. Indramayu 45273',
                        'latitude' => -6.4735000,
                        'longitude' => 108.3082000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Tegal',
                        'address' => 'Jl. Raya Kapten Sudibyo No. 80, Pekauman, Tegal Barat, Kota Tegal 52125',
                        'latitude' => -6.8745000,
                        'longitude' => 109.1332000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Cilacap',
                        'address' => 'Jl. Brigjen Katamso No. 19, Kota Cilacap 53223',
                        'latitude' => -7.7215000,
                        'longitude' => 109.0084000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Complete Selular Kroya',
                        'address' => 'Jl. Jend. Gatot Subroto No. 121, Kroya, Kab. Cilacap 53282',
                        'latitude' => -7.6321000,
                        'longitude' => 109.2485000,
                        'radius_meters' => 100,
                    ],
                ],
            ],

            // =========================================================================
            // 2. TOP - CV Top Selular (Wholesale & Principle Brand Distribution Network)
            // =========================================================================
            'TOP' => [
                'name' => 'CV Top Selular',
                'address' => 'Jl. Tentara Pelajar No. 72B, Kejaksan, Kota Cirebon, Jawa Barat 45122',
                'latitude' => -6.7161000,
                'longitude' => 108.5574000,
                'radius_meters' => 150,
                'locations' => [
                    [
                        'name' => 'HQ TOP Tentara Pelajar Cirebon',
                        'address' => 'Jl. Tentara Pelajar No. 72B, Kejaksan, Kota Cirebon 45122',
                        'latitude' => -6.7161000,
                        'longitude' => 108.5574000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'HO TOP Ocean Space PIK Jakarta',
                        'address' => 'Rukan Golf Island, Pantai Indah Kapuk, Jakarta Utara 14460',
                        'latitude' => -6.1085000,
                        'longitude' => 106.7412000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo TOP Cirebon',
                        'address' => 'Jl. Tentara Pelajar No. 72B, Cirebon 45122',
                        'latitude' => -6.7161000,
                        'longitude' => 108.5574000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo TOP Bandung (Oraimo / Realme Jabar)',
                        'address' => 'Jl. Soekarno Hatta No. 590, Buahbatu, Kota Bandung 40286',
                        'latitude' => -6.9472000,
                        'longitude' => 107.6521000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo TOP Semarang (Realme / Oraimo Jateng)',
                        'address' => 'Jl. Pemuda No. 142, Semarang Tengah, Kota Semarang 50132',
                        'latitude' => -6.9821000,
                        'longitude' => 110.4182000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo TOP Tegal',
                        'address' => 'Jl. Raya Kapten Sudibyo No. 80, Kota Tegal 52125',
                        'latitude' => -6.8745000,
                        'longitude' => 109.1332000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo TOP Purwokerto',
                        'address' => 'Jl. Jend. Sudirman No. 150, Kota Purwokerto 53116',
                        'latitude' => -7.4245000,
                        'longitude' => 109.2384000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo TOP Yogyakarta',
                        'address' => 'Jl. Laksda Adisucipto, Caturtunggal, Sleman, DIY 55281',
                        'latitude' => -7.7812000,
                        'longitude' => 110.3945000,
                        'radius_meters' => 150,
                    ],
                ],
            ],

            // =========================================================================
            // 3. MSI - PT Media Selular Indonesia (Sub-Distributor & Regional Depo Network)
            // =========================================================================
            'MSI' => [
                'name' => 'PT Media Selular Indonesia',
                'address' => 'Jl. Tuparev No. 109F, Kertawinangun, Kedawung, Kab. Cirebon, Jawa Barat 45153',
                'latitude' => -6.7088900,
                'longitude' => 108.5348800,
                'radius_meters' => 100,
                'locations' => [
                    [
                        'name' => 'HQ MSI Cirebon Kedawung',
                        'address' => 'Jl. Tuparev No. 109F, Kertawinangun, Kedawung, Cirebon 45153',
                        'latitude' => -6.7088900,
                        'longitude' => 108.5348800,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'HO MSI Ocean Space PIK Jakarta',
                        'address' => 'Rukan Golf Island, Pantai Indah Kapuk, Jakarta Utara 14460',
                        'latitude' => -6.1085000,
                        'longitude' => 106.7412000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo MSI Subang',
                        'address' => 'Jl. Otto Iskandardinata No. 56, Subang 41211',
                        'latitude' => -6.5684000,
                        'longitude' => 107.7592000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo MSI Jatibarang',
                        'address' => 'Jl. Siliwangi / Mayor Dasuki, Jatibarang, Indramayu 45273',
                        'latitude' => -6.4712000,
                        'longitude' => 108.3054000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo MSI Purwakarta',
                        'address' => 'Jl. Raya Sadang - Purwakarta, Kab. Purwakarta 41118',
                        'latitude' => -6.5321000,
                        'longitude' => 107.4425000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo MSI Jatiwangi',
                        'address' => 'Jl. Raya Jatiwangi, Majalengka 45454',
                        'latitude' => -6.7412000,
                        'longitude' => 108.2654000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo MSI Surabaya',
                        'address' => 'Jl. Raya Darmo / Pemuda, Kota Surabaya 60271',
                        'latitude' => -7.2654000,
                        'longitude' => 112.7412000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo MSI Palembang',
                        'address' => 'Jl. Jend. Sudirman, Kota Palembang 30129',
                        'latitude' => -2.9812000,
                        'longitude' => 104.7564000,
                        'radius_meters' => 150,
                    ],
                    [
                        'name' => 'Depo MSI Makassar',
                        'address' => 'Jl. AP Pettarani, Kota Makassar 90222',
                        'latitude' => -5.1485000,
                        'longitude' => 119.4321000,
                        'radius_meters' => 150,
                    ],
                ],
            ],

            // =========================================================================
            // 4. SMI - PT SATU MEDIA INDONESIA (Online, Marketing & Brand Principles)
            // =========================================================================
            'SMI' => [
                'name' => 'PT SATU MEDIA INDONESIA',
                'address' => 'Jl. Tentara Pelajar No. 72B, Kejaksan, Kota Cirebon, Jawa Barat 45122',
                'latitude' => -6.7161000,
                'longitude' => 108.5574000,
                'radius_meters' => 100,
                'locations' => [
                    [
                        'name' => 'HO SMI Cirebon Tentara Pelajar',
                        'address' => 'Jl. Tentara Pelajar No. 72B, Kejaksan, Kota Cirebon 45122',
                        'latitude' => -6.7161000,
                        'longitude' => 108.5574000,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'HO SMI Ocean Space PIK Jakarta',
                        'address' => 'Rukan Golf Island, Pantai Indah Kapuk, Jakarta Utara 14460',
                        'latitude' => -6.1085000,
                        'longitude' => 106.7412000,
                        'radius_meters' => 100,
                    ],
                ],
            ],

            // =========================================================================
            // 5. RISM - PT. RETAIL INDONESIA SELALU MAJU (Executive Retail Holding)
            // =========================================================================
            'RISM' => [
                'name' => 'PT. RETAIL INDONESIA SELALU MAJU',
                'address' => 'Jl. Tuparev No. 109F, Kertawinangun, Kedawung, Kab. Cirebon, Jawa Barat 45153',
                'latitude' => -6.7088900,
                'longitude' => 108.5348800,
                'radius_meters' => 100,
                'locations' => [
                    [
                        'name' => 'Corporate HQ RISM Cirebon',
                        'address' => 'Jl. Tuparev No. 109F, Kertawinangun, Kedawung, Cirebon 45153',
                        'latitude' => -6.7088900,
                        'longitude' => 108.5348800,
                        'radius_meters' => 100,
                    ],
                    [
                        'name' => 'Corporate Office RISM PIK Jakarta',
                        'address' => 'Rukan Golf Island, Pantai Indah Kapuk, Jakarta Utara 14460',
                        'latitude' => -6.1085000,
                        'longitude' => 106.7412000,
                        'radius_meters' => 100,
                    ],
                ],
            ],
        ];

        foreach ($companiesData as $code => $data) {
            $company = Company::where('code', $code)->first();

            if ($company) {
                // Update main company HQ coordinates & address
                $company->update([
                    'address' => $data['address'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]);

                // Ensure company policy has geofence radius configured
                if ($company->policy) {
                    $company->policy->update([
                        'require_gps' => true,
                        'geofence_radius_meters' => $data['radius_meters'],
                    ]);
                }

                // Hapus lokasi lama jika kurasi ulang penuh
                $company->locations()->delete();

                // Populate lokasi baru
                foreach ($data['locations'] as $locData) {
                    CompanyLocation::create([
                        'company_id' => $company->id,
                        'name' => $locData['name'],
                        'address' => $locData['address'],
                        'latitude' => $locData['latitude'],
                        'longitude' => $locData['longitude'],
                        'radius_meters' => $locData['radius_meters'],
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
