<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\ApprovalFlow;
use App\Models\Approver;
use App\Models\Employee;
use Illuminate\Contracts\Console\Kernel;

$emp = Employee::with(['division', 'company', 'jobTitle', 'jobLevel'])->where('full_name', 'like', '%PUTRI SUGIARTI%')->first();

if (! $emp) {
    echo "Employee not found!\n";
    exit;
}

echo "=== DATA KARYAWAN ===\n";
echo 'Nama: '.$emp->full_name."\n";
echo 'Email: '.$emp->email."\n";
echo 'Divisi: '.($emp->division?->name ?? 'Tanpa Divisi').' (ID: '.$emp->division_id.")\n";
echo 'Perusahaan: '.($emp->company?->name ?? 'Tanpa Perusahaan').' (ID: '.$emp->company_id.")\n";
echo 'Jabatan: '.($emp->jobTitle?->name ?? '-')."\n";
echo 'Level Jabatan: '.($emp->jobLevel?->name ?? '-')."\n\n";

echo "=== ATASAN / APPROVER DENGAN MAPPING ===\n";
$approvers = Approver::with(['user', 'division', 'company'])
    ->where('company_id', $emp->company_id)
    ->where(function ($q) use ($emp) {
        $q->where('division_id', $emp->division_id)
            ->orWhereNull('division_id');
    })
    ->orderBy('level')
    ->get();

if ($approvers->isEmpty()) {
    echo "Tidak ditemukan approver spesifik untuk divisi ini. Menampilkan semua approver di perusahaan:\n";
    $approvers = Approver::with(['user', 'division'])->where('company_id', $emp->company_id)->orderBy('level')->get();
}

foreach ($approvers as $app) {
    $scope = $app->division ? 'Divisi '.$app->division->name : 'Global Perusahaan';
    echo "Level {$app->level}: {$app->user->name} ({$app->user->email}) - Scope: {$scope}\n";
}

echo "\n=== ALUR APPROVAL FLOW (SISTEM) ===\n";
$flows = ApprovalFlow::with('user')->where('company_id', $emp->company_id)->orderBy('request_type')->orderBy('step_number')->get();
foreach ($flows as $flow) {
    $approverName = $flow->user ? $flow->user->name.' ('.$flow->user->email.')' : 'Role: '.$flow->approver_role;
    echo "Tipe: {$flow->request_type} | Step {$flow->step_number}: {$flow->name} -> Approver: {$approverName}\n";
}
