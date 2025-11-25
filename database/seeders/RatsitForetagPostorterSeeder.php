<?php

namespace Database\Seeders;

use App\Models\RatsitForetagPostorter;
use Illuminate\Database\Seeder;

class RatsitForetagPostorterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('import/ratsit_foretag_postorter_part_1.json');

        if (! file_exists($filePath)) {
            $this->command->error("File not found: {$filePath}");

            return;
        }

        $this->command->info('Processing ratsit_foretag_postorter_part_1.json...');

        $jsonContent = file_get_contents($filePath);
        $records = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Invalid JSON in file: {$filePath}");

            return;
        }

        $this->command->info('Found ' . count($records) . ' records');

        // Insert records in chunks to avoid memory issues
        $chunkSize = 1000;
        $chunks = array_chunk($records, $chunkSize);

        foreach ($chunks as $chunk) {
            $insertData = array_map(function ($record) {
                return [
                    'post_ort' => $record['post_ort'],
                    'post_nummer' => $record['post_nummer'],
                    'foretag_count' => $record['foretag_count'],
                    'ratsit_link' => $record['ratsit_link'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $chunk);

            RatsitForetagPostorter::insert($insertData);
        }

        $this->command->info('RatsitForetagPostorter seeding completed!');
    }
}
