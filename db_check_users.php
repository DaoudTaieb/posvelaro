<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = DB::table('users')->select('login', 'password')->get();
echo "=== USERS ===\n";
foreach($users as $u) {
    echo "Login: '" . $u->login . "', Password: '" . $u->password . "'\n";
}
