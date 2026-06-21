<?php
$tables = ['defaultconfigs', 'backconfigs', 'retailconfigs', 'param'];
foreach ($tables as $t) {
    echo "TABLE: $t\n";
    echo json_encode(\Illuminate\Support\Facades\DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='public' AND table_name='$t'"));
    echo "\n\n";
}
