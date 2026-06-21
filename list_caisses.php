<?php
echo json_encode(DB::table('caisses')->limit(5)->get());
