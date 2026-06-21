<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
foreach ($tables as $table) {
    if (strpos($table->table_name, 'caisse') !== false || strpos($table->table_name, 'ticket') !== false || strpos($table->table_name, 'vente') !== false) {
        echo $table->table_name . "\n";
    }
}
