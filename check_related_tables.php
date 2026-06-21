<?php
$tables = ['sites', 'agencebs', 'agences', 'clients', 'machines'];
$res = \Illuminate\Support\Facades\DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
$found = [];
foreach ($res as $row) {
    if (in_array($row->table_name, $tables)) {
        $found[] = $row->table_name;
    }
}
echo json_encode($found);
