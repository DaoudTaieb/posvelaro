<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='public' AND table_name='clients' ORDER BY ordinal_position");
echo "=== CLIENTS TABLE ===\n";
foreach ($cols as $c) {
    echo $c->column_name . " : " . $c->data_type . "\n";
}

$sample = DB::table('clients')->first();
echo "\n=== SAMPLE ===\n";
if ($sample) {
    foreach ((array)$sample as $k => $v) {
        echo $k . " = " . $v . "\n";
    }
}
