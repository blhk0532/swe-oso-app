<?php

namespace App\Console\Commands;

use App\Models\PostNummer;
use Illuminate\Console\Command;

class ImportFirst3PostNummer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postnummer:import-first3 {--path= : Optional CSV path; defaults to database/sweden.csv} {--truncate : Truncate table before import} {--log-every=100 : Log every N rows}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import ALL rows from sweden.csv using only the first 3 columns (postnummer, postort, post_lan) without deleting existing data.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->option('path') ?: base_path('database/sweden.csv');
        $logEvery = (int) $this->option('log-every');

        if (! file_exists($path)) {
            $this->error("CSV not found: {$path}");

            return self::FAILURE;
        }

        // Truncate table if requested
        if ($this->option('truncate')) {
            $this->warn('Truncating post_nummer table...');
            PostNummer::truncate();
            $this->info('Table truncated.');
        }

        $this->info("Reading: {$path}");

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        if (($handle = fopen($path, 'r')) === false) {
            $this->error('Unable to open CSV file.');

            return self::FAILURE;
        }

        $row = 0;
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data);

            // Take only first 3 columns; others ignored
            $postNummer = $data[0] ?? null;
            $postOrt = $data[1] ?? null;
            $postLan = $data[2] ?? null;

            // Skip blank / malformed lines
            if (! $postNummer || ! $postOrt) {
                $skipped++;
                $row++;

                continue;
            }

            // Skip header-like rows (heuristic: non-numeric first column)
            if (! ctype_digit(str_replace(' ', '', $postNummer))) {
                $skipped++;
                $row++;

                continue;
            }

            $existing = PostNummer::query()->where('post_nummer', $postNummer)->first();

            if ($existing) {
                // Only update post_ort if changed to minimize writes
                $changes = [];
                if ($existing->post_ort !== $postOrt) {
                    $changes['post_ort'] = $postOrt;
                }
                if (($existing->post_lan ?? null) !== $postLan) {
                    $changes['post_lan'] = $postLan;
                }

                if (! empty($changes)) {
                    $existing->update($changes);
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                PostNummer::create([
                    'post_nummer' => $postNummer,
                    'post_ort' => $postOrt,
                    'post_lan' => $postLan,
                    'is_active' => false,
                    'is_pending' => false,
                    'is_complete' => false,
                ]);
                $inserted++;
            }

            $row++;

            // Log progress at intervals
            if ($logEvery > 0 && $row % $logEvery === 0) {
                $this->line("Progress: {$row} rows processed (Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped})");
            }
        }

        fclose($handle);

        $this->info("Done. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped} (processed {$row} rows using first 3 columns)");

        return self::SUCCESS;
    }
}
