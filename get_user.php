<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = DB::table('users')->first();
echo "Login: " . $user->login . "\n";
echo "Email: " . $user->email . "\n";
// Passwords in Laravel are hashed, we can't reverse them.
