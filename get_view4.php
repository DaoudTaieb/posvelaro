<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$viewDef = DB::selectOne("SELECT pg_get_viewdef('pointagedetdemandetransfertsviews', true) as def");
echo "VIEW DEF:\n" . $viewDef->def . "\n";
