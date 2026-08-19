<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

$users = User::whereIn('email', ['noverina@ontime.com', 'firmansyahbana@ontime.com', 'putrisugiarti@ontime.com'])->get();
foreach ($users as $u) {
    echo "User: {$u->name} | Email: {$u->email} | Roles: ".$u->getRoleNames()->implode(', ')."\n";
}
