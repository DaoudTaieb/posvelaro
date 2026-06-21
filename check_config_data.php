<?php
$b = \Illuminate\Support\Facades\DB::select("SELECT * FROM backconfigs WHERE pagelibelle='Général' LIMIT 5");
echo "backconfigs:\n";
echo json_encode($b);

$r = \Illuminate\Support\Facades\DB::select("SELECT * FROM retailconfigs WHERE pagelibelle='Général' LIMIT 5");
echo "\nretailconfigs:\n";
echo json_encode($r);
