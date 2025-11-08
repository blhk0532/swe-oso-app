<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostNummerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert post_nummer values from 10000 to 99999 in batches for performance
        $batchSize = 1000;
        $data = [];

        for ($postNummer = 10000; $postNummer <= 99999; $postNummer++) {
            $data[] = [
                'post_nummer' => (string) $postNummer,
                'post_ort' => null,
                'total_count' => 0,
                'status' => 'pending',
                'is_pending' => true,
                'is_complete' => false,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches
            if (count($data) >= $batchSize) {
                DB::table('post_nummer')->insert($data);
                $data = [];
            }
        }

        // Insert any remaining records
        if (! empty($data)) {
            DB::table('post_nummer')->insert($data);
        }

        $this->command->info('Successfully seeded ' . (99999 - 10000 + 1) . ' post nummer records.');
    }
}
