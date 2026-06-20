<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$res = \Illuminate\Support\Facades\DB::select("SELECT cticketdate FROM ctickets LEFT JOIN clients ON ctickets.clientid = clients.clientid WHERE clients.nom ILIKE '%afef%' OR clients.prenom ILIKE '%afef%'");
echo json_encode($res);
