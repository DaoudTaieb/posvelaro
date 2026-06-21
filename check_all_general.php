<?php
$r = \Illuminate\Support\Facades\DB::select("SELECT * FROM retailconfigs WHERE pagelibelle='Général' ORDER BY ordreaffichage");
echo json_encode($r, JSON_PRETTY_PRINT);
