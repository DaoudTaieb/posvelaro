<?php
$tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
$res = [];
foreach ($tables as $t) {
    if (strpos($t->table_name, 'barcode') !== false || strpos($t->table_name, 'code') !== false || strpos($t->table_name, 'barre') !== false) {
        $res[] = $t->table_name;
    }
}
echo json_encode($res);
