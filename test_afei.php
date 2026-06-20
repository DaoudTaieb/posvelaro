<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$res = \Illuminate\Support\Facades\DB::select("SELECT nom, prenom FROM clients WHERE nom ILIKE '%afei%' OR prenom ILIKE '%afei%'");
echo json_encode($res);
