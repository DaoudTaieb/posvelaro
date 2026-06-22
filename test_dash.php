<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $c = new \App\Http\Controllers\DashboardController();
    $res = $c->index();
    $html = $res->render();
    echo "SUCCESS: Blade rendered successfully. Length: " . strlen($html) . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
