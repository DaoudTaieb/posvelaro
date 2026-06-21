<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['detctickets', 'creglements', 'modereglements', 'modalitereglements'];
foreach($tables as $tbl) {
    echo "--- $tbl ---\n";
    $columns = \Illuminate\Support\Facades\DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='public' AND table_name='$tbl'");
    foreach($columns as $c) {
        echo $c->column_name . " (" . $c->data_type . ")\n";
    }
}
