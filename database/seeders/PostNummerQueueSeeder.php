<?php

namespace Database\Seeders;

use App\Models\PostNummerQueue;
use Illuminate\Database\Seeder;

class PostNummerQueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $importPath = database_path('import');
        $personJsonPath = $importPath . '/ratsit_person_postorter.json';
        $foretagJsonPath = $importPath . '/ratsit_foretag_postorter.json';

        // Load totals from JSON files
        $totals = [];
        if (file_exists($personJsonPath)) {
            $personData = json_decode(file_get_contents($personJsonPath), true);
            foreach ($personData as $obj) {
                $pn = preg_replace('/\s+/', '', (string) $obj['post_nummer']);
                $totals[$pn]['ratsit_personer_total'] = $obj['ratsit_personer_total'] ?? 0;
            }
        }
        if (file_exists($foretagJsonPath)) {
            $foretagData = json_decode(file_get_contents($foretagJsonPath), true);
            foreach ($foretagData as $obj) {
                $pn = preg_replace('/\s+/', '', (string) $obj['post_nummer']);
                $totals[$pn]['ratsit_foretag_total'] = $obj['ratsit_foretag_total'] ?? ($obj['foretag_count'] ?? 0);
            }
        }

        // Loop through all 10 parts
        for ($part = 1; $part <= 10; $part++) {
            $filePath = $importPath . "/postnummer_az_part_{$part}.json";

            if (! file_exists($filePath)) {
                $this->command->error("File not found: {$filePath}");

                continue;
            }

            $this->command->info("Processing part {$part}...");

            $jsonContent = file_get_contents($filePath);
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->command->error("Invalid JSON in file: {$filePath}");

                continue;
            }

            if (! isset($data['records']) || ! is_array($data['records'])) {
                $this->command->error("No records found in file: {$filePath}");

                continue;
            }

            $records = $data['records'];
            $this->command->info('Found ' . count($records) . " records in part {$part}");

            // Insert records in chunks to avoid memory issues
            $chunkSize = 100;
            $chunks = array_chunk($records, $chunkSize);

            foreach ($chunks as $chunk) {
                $insertData = array_map(function ($record) use ($totals) {
                    $normalizedPn = preg_replace('/\s+/', '', (string) $record['post_nummer']);
                    $recordTotals = $totals[$normalizedPn] ?? [];

                    return [
                        'id' => $record['id'],
                        'post_nummer' => $normalizedPn,
                        'post_ort' => $record['post_ort'],
                        'post_lan' => $record['post_lan'],
                        'ratsit_personer_total' => $recordTotals['ratsit_personer_total'] ?? 0,
                        'ratsit_foretag_total' => $recordTotals['ratsit_foretag_total'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $chunk);

                PostNummerQueue::insert($insertData);
            }

            $this->command->info("Completed processing part {$part}");
        }

        $this->command->info('PostNummer seeding completed!');
    }
}
