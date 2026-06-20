<?php
$views = DB::select("SELECT table_name FROM information_schema.views WHERE table_schema = 'public'");
$res = [];
foreach($views as $v) {
    if (strpos($v->table_name, 'stock') !== false) {
        $res[] = $v->table_name;
    }
}
echo json_encode($res);
