<?php

namespace Database\Seeders;

use App\Models\RatsitPersonKommuner;
use Illuminate\Database\Seeder;

class RatsitPersonKommunerSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('import/ratsit_person_kommuner.json');

        if (! file_exists($filePath)) {
            $this->command->error("File not found: {$filePath}");

            return;
        }

        $this->command->info('Processing ratsit_person_kommuner.json...');

        $jsonContent = file_get_contents($filePath);
        $records = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("Invalid JSON in file: {$filePath}");

            return;
        }

        $this->command->info('Found ' . count($records) . ' records');

        // Insert records in chunks to avoid memory issues
        $chunkSize = 100;
        $chunks = array_chunk($records, $chunkSize);

        foreach ($chunks as $chunk) {
            $insertData = array_map(function ($record) {
                return [
                    'kommun' => $record['kommun'],
                    'person_count' => $record['personer_count'],
                    'ratsit_link' => $record['ratsit_link'],
                    'person_postort_saved' => $record['post_ort_saved'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $chunk);

            RatsitPersonKommuner::insert($insertData);
        }

        $this->command->info('RatsitPersonKommuner seeding completed!');
    }
}
