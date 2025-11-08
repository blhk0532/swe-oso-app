<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdatePostNummerFromSwedenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting to update post_nummer table from sweden table...');

        // Get all records from sweden table grouped by post_nummer
        // If there are multiple entries for the same post_nummer, we'll take the first one
        $swedenData = DB::table('sweden')
            ->select('post_nummer', 'post_ort', 'post_lan')
            ->groupBy('post_nummer', 'post_ort', 'post_lan')
            ->get();

        $updated = 0;
        $notFound = 0;

        foreach ($swedenData as $data) {
            $result = DB::table('post_nummer')
                ->where('post_nummer', $data->post_nummer)
                ->update([
                    'post_ort' => $data->post_ort,
                    'post_lan' => $data->post_lan,
                ]);

            if ($result > 0) {
                $updated++;

                if ($updated % 100 === 0) {
                    $this->command->info("Updated {$updated} records...");
                }
            } else {
                $notFound++;
            }
        }

        $this->command->info("Successfully updated {$updated} records in post_nummer table");
        $this->command->info("Skipped {$notFound} records (not found in post_nummer table)");
    }
}
