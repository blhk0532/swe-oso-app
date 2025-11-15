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

        // Loop through all 10 parts
        for ($part = 1; $part <= 10; $part++) {
            $filePath = $importPath . "/postnummer_desc_part_{$part}.json";

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
            $chunkSize = 100; // Reduced to avoid max_allowed_packet issues
            $chunks = array_chunk($records, $chunkSize);

            foreach ($chunks as $chunk) {
                $insertData = array_map(function ($record) {
                    return [
                        // Normalize to 5-digit format for queue tables (strip spaces)
                        'post_nummer' => preg_replace('/\s+/', '', (string) $record['post_nummer']),
                        'post_ort' => $record['post_ort'],
                        'post_lan' => $record['post_lan'],
                        'merinfo_personer_saved' => null,
                        'merinfo_foretag_saved' => null,
                        'merinfo_personer_total' => null,
                        'merinfo_foretag_total' => null,
                        'merinfo_status' => null,
                        'merinfo_checked' => false,
                        'merinfo_queued' => false,
                        'merinfo_scraped' => false,
                        'merinfo_complete' => false,
                        'ratsit_personer_saved' => null,
                        'ratsit_foretag_saved' => null,
                        'ratsit_personer_total' => null,
                        'ratsit_foretag_total' => null,
                        'ratsit_status' => null,
                        'ratsit_checked' => false,
                        'ratsit_queued' => false,
                        'ratsit_scraped' => false,
                        'ratsit_complete' => false,
                        'hitta_personer_saved' => null,
                        'hitta_foretag_saved' => null,
                        'hitta_personer_total' => null,
                        'hitta_foretag_total' => null,
                        'hitta_status' => null,
                        'hitta_checked' => false,
                        'hitta_queued' => false,
                        'hitta_scraped' => false,
                        'hitta_complete' => false,
                        'post_nummer_personer_saved' => null,
                        'post_nummer_foretag_saved' => null,
                        'post_nummer_personer_total' => null,
                        'post_nummer_foretag_total' => null,
                        'post_nummer_status' => null,
                        'post_nummer_checked' => false,
                        'post_nummer_queued' => false,
                        'post_nummer_scraped' => false,
                        'post_nummer_complete' => false,
                        'is_active' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $chunk);

                PostNummerQueue::insert($insertData);
            }

            $this->command->info("Completed processing part {$part}");
        }

        $this->command->info('PostNummerQueue seeding completed!');
    }
}
