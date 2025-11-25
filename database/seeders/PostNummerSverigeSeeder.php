<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PDO;

class PostNummerSverigeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Import data from SQLite database
        $sqlitePath = database_path('post_nummer.sqlite');

        if (file_exists($sqlitePath)) {
            $pdo = new PDO('sqlite:' . $sqlitePath);
            $data = $pdo->query('SELECT * FROM postnummer')->fetchAll(PDO::FETCH_OBJ);

            $this->command->info('Starting import of ' . count($data) . ' post_nummers records...');

            // Clear existing data and use transactions for better performance
            PostSverigeNummer::truncate();

            DB::transaction(function () use ($data) {
                foreach ($data as $record) {
                    PostSverigeNummer::create([
                        'id' => $record->id,
                        'post_nummer' => $record->post_nummer,
                        'post_ort' => $record->post_ort,
                        'post_lan' => $record->post_lan,
                    ]);
                }
            });

            $this->command->info('Successfully imported ' . count($data) . ' post_nummer records.');
        } else {
            $this->command->error('SQLite database file not found: ' . $sqlitePath);
        }
    }
}
