<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Exports\AttendanceReportExport;
use App\Models\Attendance;
use Illuminate\Contracts\Console\Kernel;

$records = Attendance::with(['employee.company', 'employee.division'])->limit(5)->get();
echo 'Exporting '.$records->count()." records to Excel...\n";

try {
    $export = new AttendanceReportExport($records);
    $drawings = $export->drawings();
    echo 'Total drawings generated: '.count($drawings)."\n";
    echo "SUCCESS!\n";
} catch (Throwable $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
}
