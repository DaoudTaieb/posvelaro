<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employees = \Illuminate\Support\Facades\DB::table('employees')->get();
foreach($employees as $e) {
    echo "ID: {$e->employeeid} | Code: " . ($e->code ?? 'NULL') . " | Nom: " . ($e->nom ?? 'NULL') . " | Site: " . ($e->siteid ?? 'NULL') . " | Vendeur: " . ($e->isvendeur ?? 'NULL') . PHP_EOL;
}
