<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SwedenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('sweden.csv');

        if (! file_exists($csvFile)) {
            $this->command->error('CSV file not found at: ' . $csvFile);

            return;
        }

        $file = fopen($csvFile, 'r');

        if ($file === false) {
            $this->command->error('Failed to open CSV file');

            return;
        }

        DB::table('sweden')->truncate();

        $batch = [];
        $batchSize = 1000;
        $count = 0;

        while (($data = fgetcsv($file)) !== false) {
            if (count($data) >= 2) {
                $batch[] = [
                    'post_nummer' => trim($data[0]),
                    'post_ort' => trim($data[1]),
                    'post_lan' => isset($data[2]) ? trim($data[2]) : null,
                ];

                $count++;

                if (count($batch) >= $batchSize) {
                    DB::table('sweden')->insert($batch);
                    $batch = [];
                    $this->command->info("Imported {$count} records...");
                }
            }
        }

        if (! empty($batch)) {
            DB::table('sweden')->insert($batch);
        }

        fclose($file);

        $this->command->info("Successfully imported {$count} records from sweden.csv");
    }
}
