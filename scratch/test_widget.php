<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Filament\Widgets\UserProfileWidget;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;

$emails = ['admin@ontime.com', 'noverina@ontime.com', 'putrisugiarti@ontime.com'];
foreach ($emails as $email) {
    $u = User::where('email', $email)->first();
    if ($u) {
        auth()->login($u);
        $w = new UserProfileWidget;
        echo "=== USER: {$u->name} ({$email}) ===\n";
        print_r($w->getUserInfo());
        echo "\n";
    }
}
