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

// Update post_nummer format from XXXXX to XXX XX
$rows = $db->table('post_nummer')->get();
$updatedCount = 0;
foreach ($rows as $row) {
    $old = $row->post_nummer;
    if (preg_match('/^\d{5}$/', $old)) {
        $new = substr($old, 0, 3) . ' ' . substr($old, 3, 2);
        if ($new !== $old) {
            $db->table('post_nummer')->where('id', $row->id)->update(['post_nummer' => $new]);
            $updatedCount++;
        }
    }
}
echo "Updated {$updatedCount} post_nummer formats in post_nummer table\n";

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

// Sync count, phone, house fields from ratsit_data and hitta_se
$postNummers = $db->table('post_nummer')->get();
$syncCount = 0;
foreach ($postNummers as $row) {
    $pn = $row->post_nummer;
    // Remove space for matching
    $pn_compact = str_replace(' ', '', $pn);

    // Count in ratsit_data
    $ratsitCount = $db->table('ratsit_data')->where('postnummer', $pn)->count();
    $ratsitPhones = $db->table('ratsit_data')->where('postnummer', $pn)->pluck('telefon');
    $ratsitPhoneCount = 0;
    foreach ($ratsitPhones as $phones) {
        if (is_string($phones)) {
            $arr = json_decode($phones, true);
            if (is_array($arr)) $ratsitPhoneCount += count($arr);
        }
    }

    // Count in hitta_se
    $hittaCount = $db->table('hitta_se')->where('postnummer', $pn)->count();
    $hittaPhones = $db->table('hitta_se')->where('postnummer', $pn)->pluck('telefon');
    $hittaPhoneCount = 0;
    foreach ($hittaPhones as $phones) {
        if (is_string($phones)) {
            $arr = json_decode($phones, true);
            if (is_array($arr)) $hittaPhoneCount += count($arr);
        }
    }

    // House count: count records in hitta_se where bostadstyp contains house-related keywords
    $hittaRecords = $db->table('hitta_se')->where('postnummer', $pn)->whereNotNull('bostadstyp')->get();
    $hittaHouseCount = 0;
    foreach ($hittaRecords as $record) {
        $type = strtolower($record->bostadstyp ?? '');
        if (preg_match('/\bhus\b|villa|radhus|friliggande|kedjehus/i', $type)) {
            $hittaHouseCount++;
        }
    }

    // Update post_nummer table
    $db->table('post_nummer')->where('id', $row->id)->update([
        'count' => $ratsitCount + $hittaCount,
        'phone' => $ratsitPhoneCount + $hittaPhoneCount,
        'house' => $hittaHouseCount,
    ]);
    $syncCount++;
}
echo "Synced count, phone, house for {$syncCount} post_nummer rows\n";

$total = $db->table('post_nummer')->count();
echo "Total post_nummer rows: {$total}\n";

// Done
echo "Done.\n";
