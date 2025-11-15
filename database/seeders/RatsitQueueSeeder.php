<?php

namespace Database\Seeders;

use App\Models\RatsitQueue;
use Illuminate\Database\Seeder;

class RatsitQueueSeeder extends Seeder
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
            $chunkSize = 1000;
            $chunks = array_chunk($records, $chunkSize);

            foreach ($chunks as $chunk) {
                $insertData = array_map(function ($record) {
                    return [
                        'post_nummer' => $record['post_nummer'],
                        'post_ort' => $record['post_ort'],
                        'post_lan' => $record['post_lan'],
                        'foretag_total' => 0,
                        'personer_total' => 0,
                        'foretag_phone' => 0,
                        'personer_phone' => 0,
                        'personer_house' => 0,
                        'foretag_saved' => 0,
                        'personer_saved' => 0,
                        'personer_status' => null,
                        'foretag_status' => null,
                        'foretag_scraped' => false,
                        'personer_scraped' => false,
                        'foretag_queued' => false,
                        'personer_queued' => false,
                        'is_active' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $chunk);

                RatsitQueue::insert($insertData);
            }

            $this->command->info("Completed processing part {$part}");
        }

        $this->command->info('RatsitQueue seeding completed!');
    }
}
