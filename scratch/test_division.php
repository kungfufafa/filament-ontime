    <?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Division;

$div = Division::first();
if ($div) {
    echo "Division found: " . $div->name . "\n";
    $list = $div->descendantsAndSelf()->pluck('name')->toArray();
    echo "Descendants and self: " . implode(', ', $list) . "\n";
    echo "SUCCESS!\n";
} else {
    echo "No division found!\n";
}
