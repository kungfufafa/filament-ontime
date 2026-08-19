<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\ApprovalFlow;
use Illuminate\Contracts\Console\Kernel;

$types = ['leave', 'overtime', 'correction'];
foreach ($types as $t) {
    $count = ApprovalFlow::where('company_id', 1)->where('request_type', $t)->count();
    echo "Type {$t}: {$count} items\n";
}
