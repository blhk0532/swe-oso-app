<?php

namespace App\Jobs;

use App\Events\PostNummerStatusUpdated;
use App\Models\PostNummer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable as FoundationQueueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class RunHittaCountForPostNummer implements ShouldQueue
{
    use Dispatchable;
    use FoundationQueueable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300; // 5 minutes

    public int $tries = 1;

    public function __construct(public PostNummer $postNummer)
    {
        $this->onQueue('postnummer');
    }

    public function handle(): void
    {
        $postnummer = $this->postNummer->fresh();

        if (! $postnummer) {
            return;
        }

        $query = trim($postnummer->post_nummer . ' ' . $postnummer->post_ort);
        $script = base_path('resources/scripts/hittaCounts.mjs');

        $process = Process::timeout(300)->run([
            'node',
            $script,
            $query,
        ]);

        if (! $process->successful()) {
            throw new RuntimeException('Failed to run hittaCount script: ' . $process->errorOutput());
        }

        $counts = $this->extractCountsFromOutput($process->output());

        if ($counts === null) {
            throw new RuntimeException('Failed to extract counts from output: ' . $process->output());
        }

        $postnummer->update([
            'total_count' => $counts['hittaForetag'] ?? 0,
            'bolag' => $counts['hittaForetag'] ?? 0,
        ]);

        event(new PostNummerStatusUpdated($postnummer));
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
