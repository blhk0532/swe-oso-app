<?php

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Database\DatabaseManager;
use Illuminate\Redis\Connections\Connection;

// scripts/reset_postnummer.php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

/** @var Repository $config */
$config = $app['config'];
$driver = (string) $config->get('queue.default');
echo "Queue connection: {$driver}\n";

/** @var DatabaseManager $db */
$db = $app['db'];

if ($driver === 'database') {
    $count = $db->table('jobs')
        ->where('payload', 'like', '%App\\\\Jobs\\\\UpdatePostNummerFromSweden%')
        ->delete();
    echo "Deleted {$count} matching jobs from jobs table\n";
} elseif ($driver === 'redis') {
    $queue = (string) $config->get('queue.connections.redis.queue', 'default');
    $key = 'queues:' . $queue;
    $removed = 0;

    try {
        /** @var Connection $redis */
        $redis = $app['redis']->connection();
        $items = $redis->lrange($key, 0, -1);
        foreach ($items as $item) {
            if (strpos($item, 'App\\\\Jobs\\\\UpdatePostNummerFromSweden') !== false) {
                $redis->lrem($key, 0, $item);
                $removed++;
            }
        }
        echo "Removed {$removed} matching items from Redis key {$key}\n";
    } catch (Throwable $e) {
        echo 'Redis error: ' . $e->getMessage() . "\n";
    }
} else {
    echo "Queue driver is '{$driver}' - no automated clearing applied (sync/null/other)\n";
}

// Reset post_nummer rows
$updated = $db->table('post_nummer')->update([
    'status' => null,
    'is_active' => false,
    'progress' => 0,
    'count' => 0,
    'total_count' => 0,
    'is_pending' => true,
    'is_complete' => false,
]);
echo "Updated {$updated} rows in post_nummer table\n";

// Clear progress cache
$app['cache']->forget('update_post_nummer_progress');
echo "Cleared cache key 'update_post_nummer_progress'\n";

// Verify cache works by writing and reading a test value
$app['cache']->put('update_post_nummer_progress', [
    'status' => 'running',
    'total' => 10,
    'updated' => 1,
    'skipped' => 0,
    'processed' => 1,
    'percentage' => 10,
    'message' => 'test',
], 3600);
$val = $app['cache']->get('update_post_nummer_progress');
echo 'Cache readback: ';
var_export($val);
echo "\n";

// Show counts for sanity
$total = $db->table('post_nummer')->count();
echo "Total post_nummer rows: {$total}\n";

// Done
echo "Done.\n";
