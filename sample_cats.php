<?php
echo "cats:\n";
echo json_encode(DB::table('categories')->limit(2)->get());
echo "\ncats2:\n";
echo json_encode(DB::table('categories2')->limit(2)->get());
echo "\ncats3:\n";
echo json_encode(DB::table('categories3')->limit(2)->get());
echo "\ncats4:\n";
echo json_encode(DB::table('categories4')->limit(2)->get());
