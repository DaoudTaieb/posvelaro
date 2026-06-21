<?php
$tables = ['userdroits', 'typedroits', 'userdroitdets'];
$res = [];
foreach ($tables as $t) {
    $res[$t] = \Illuminate\Support\Facades\DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema='public' AND table_name='$t'");
}
echo json_encode($res);
