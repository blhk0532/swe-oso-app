<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

/** @var User $user */
$user = User::first();

if (! $user) {
    $hash = Hash::make('password');
    $user = User::create([
        'name' => 'Scraper',
        'email' => 'scraper@example.com',
        'password' => $hash,
    ]);
}

$token = $user->createToken('ratsit-scraper')->plainTextToken;
fwrite(STDOUT, $token . PHP_EOL);
