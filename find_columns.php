<?php
$cols = DB::select("SELECT table_name, column_name FROM information_schema.columns WHERE (column_name LIKE '%ecart%' OR column_name LIKE '%achat%' OR column_name LIKE '%vente%') AND table_schema = 'public'");
$res = [];
foreach($cols as $c) {
    if(!isset($res[$c->table_name])) $res[$c->table_name] = [];
    $res[$c->table_name][] = $c->column_name;
}
echo json_encode($res, JSON_PRETTY_PRINT);
