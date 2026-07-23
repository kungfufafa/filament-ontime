<?php

use App\Models\ApprovalFlow;
use App\Models\Company;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            ApprovalFlow::where('company_id', $company->id)->delete();

            foreach (['leave' => 'Cuti / Izin', 'overtime' => 'Lembur', 'correction' => 'Koreksi Absensi'] as $requestType => $label) {
                ApprovalFlow::create([
                    'company_id' => $company->id,
                    'request_type' => $requestType,
                    'step_number' => 1,
                    'step_order' => 1,
                    'name' => "Persetujuan Approver {$label}",
                    'approver_type' => 'role',
                    'approver_role' => 'Approver',
                ]);

                ApprovalFlow::create([
                    'company_id' => $company->id,
                    'request_type' => $requestType,
                    'step_number' => 2,
                    'step_order' => 2,
                    'name' => "Persetujuan BOD {$label}",
                    'approver_type' => 'role',
                    'approver_role' => 'BOD',
                ]);

                ApprovalFlow::create([
                    'company_id' => $company->id,
                    'request_type' => $requestType,
                    'step_number' => 3,
                    'step_order' => 3,
                    'name' => "Persetujuan Akhir Superadmin {$label}",
                    'approver_type' => 'role',
                    'approver_role' => 'Superadmin',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed as standard 3-stage flow is required system-wide.
    }
};
