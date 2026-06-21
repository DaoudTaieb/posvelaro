<?php
$tables = \Illuminate\Support\Facades\DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema='public'");
$tableNames = array_column($tables, 'table_name');
echo json_encode($tableNames);
