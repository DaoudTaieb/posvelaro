<?php
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE 'categor%'");
$res = [];
foreach ($tables as $t) {
    $res[] = $t->table_name;
}
echo json_encode($res);
