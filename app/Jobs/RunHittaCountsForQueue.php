<?php

namespace App\Jobs;

use App\Models\HittaQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class RunHittaCountsForQueue implements ShouldQueue
{
    use Dispatchable;
    use FoundationQueueable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300; // 5 minutes

    public int $tries = 1;

    public function __construct(public HittaQueue $hittaQueue)
    {
        $this->onQueue('hitta-queue');
    }

    public function handle(): void
    {
        $record = $this->hittaQueue->fresh();

        if (! $record) {
            return;
        }

        Log::info("[Hitta Counts Queue {$record->post_nummer}] Starting count extraction");

        // Update status to running
        $record->update([
            'personer_status' => 'running',
            'is_active' => true,
        ]);

        // Use post_nummer for search
        $query = trim($record->post_nummer);
        $script = base_path('scripts/hittaCounts.mjs');

        $process = Process::timeout(300)->run([
            'node',
            $script,
            $query,
        ]);

        if (! $process->successful()) {
            Log::error("[Hitta Counts Queue {$record->post_nummer}] Failed: " . $process->errorOutput());
            $record->update([
                'personer_status' => 'failed',
                'is_active' => false,
            ]);

            throw new RuntimeException('Failed to run hittaCount script: ' . $process->errorOutput());
        }

        $counts = $this->extractCountsFromOutput($process->output());

        if ($counts === null) {
            Log::error("[Hitta Counts Queue {$record->post_nummer}] Failed to extract counts from output: " . $process->output());
            $record->update([
                'personer_status' => 'failed',
                'is_active' => false,
            ]);

            throw new RuntimeException('Failed to extract counts from output: ' . $process->output());
        }

        Log::info("[Hitta Counts Queue {$record->post_nummer}] Extracted counts: " . json_encode($counts));

        // Update record with counts
        $record->update([
            'personer_total' => $counts['hittaPersoner'] ?? 0,
            'foretag_total' => $counts['hittaForetag'] ?? 0,
            'personer_status' => 'complete',
            'is_active' => false,
        ]);

        Log::info("[Hitta Counts Queue {$record->post_nummer}] Count extraction completed");
    }

    /**
     * Attempt to extract the JSON counts object from mixed console output.
     *
     * @return array<string,int>|null
     */
    protected function extractCountsFromOutput(string $output): ?array
    {
        // Take the last line that looks like a JSON object
        $lines = array_filter(array_map('trim', explode("\n", $output)));
        $lines = array_reverse($lines);
        foreach ($lines as $line) {
            if (str_starts_with($line, '{') && str_ends_with($line, '}')) {
                $data = json_decode($line, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        }

        // Fallback: try to find the first {...} block in the output
        if (preg_match('/\{.*\}/s', $output, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data)) {
                return $data;
            }
        }

        return null;
    }
}
