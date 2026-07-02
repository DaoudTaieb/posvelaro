<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['demandetransferts', 'bontransferts'];
foreach ($tables as $table) {
    echo "=== $table ===\n";
    $columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = ?", [$table]);
    foreach ($columns as $column) {
        echo $column->column_name . " (" . $column->data_type . ")\n";
    }
}
