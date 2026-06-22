<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");

$schema = [];
foreach($tables as $t) {
    $tbl = $t->table_name;
    $columns = \Illuminate\Support\Facades\DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='public' AND table_name=?", [$tbl]);
    $cols = [];
    foreach($columns as $c) {
        $cols[] = $c->column_name . ' (' . $c->data_type . ')';
    }
    $schema[$tbl] = $cols;
}

file_put_contents(__DIR__ . '/db_schema_utf8.json', json_encode($schema, JSON_PRETTY_PRINT));
echo "Done\n";
