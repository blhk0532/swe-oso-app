<?php

namespace Database\Seeders;

use App\Models\RatsitKommunSverige;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class RatsitKommunerSverigeSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('import/seeders/ratsit_kommuner_sverige_export_2025-11-17_235059.csv');

        if (! file_exists($csvPath)) {
            $this->command->error('CSV file not found: ' . $csvPath);

            return;
        }

        $this->command->info('Importing ratsit kommuner (Sverige) from CSV...');

        $file = new SplFileObject($csvPath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        // Truncate existing
        DB::table('ratsit_kommuner_sverige')->truncate();

        $header = null;
        foreach ($file as $row) {
            if (! is_array($row)) {
                continue;
            }
            if ($row === [null]) {
                continue;
            }

            if (! $header) {
                $header = $row;

                continue;
            }

            if (count($header) !== count($row)) {
                // mismatched CSV row - skip
                continue;
            }

            $data = array_combine($header, $row);

            if (! $data) {
                continue;
            }

            RatsitKommunSverige::updateOrCreate(
                ['kommun' => $data['kommun']],
                [
                    'kommun' => $data['kommun'],
                    'post_ort_saved' => (int) ($data['post_ort_saved'] ?? 0),
                    'personer_total' => (int) ($data['personer_total'] ?? 0),
                    'ratsit_link' => $data['ratsit_link'] ?: null,
                    'created_at' => $data['created_at'] ?: now(),
                    'updated_at' => $data['updated_at'] ?: now(),
                ]
            );
        }

        $this->command->info('Imported ratsit kommuner (Sverige).');
    }
}
