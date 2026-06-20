<?php
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
$res = [];
foreach ($tables as $t) {
    if (strpos($t->table_name, 'stock') !== false) {
        $res[] = $t->table_name;
    }
}
echo json_encode($res);
