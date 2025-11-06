<?php

use App\Models\RatsitData;
use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

/** @var Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo (string) RatsitData::count(), PHP_EOL;
