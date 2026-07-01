<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::statement("SELECT setval(pg_get_serial_sequence('journalcaisses', 'journalcaisseid'), coalesce(max(journalcaisseid), 0) + 1, false) FROM journalcaisses;");
echo "Sequence updated.\n";
