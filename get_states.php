<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Demande Etats: '.json_encode(DB::table('etatdemandetransferts')->get()).PHP_EOL;
try {
    echo 'Bon Etats: '.json_encode(DB::table('etatbontransferts')->get()).PHP_EOL;
} catch (\Exception $e) {
    echo "No etatbontransferts table\n";
}
