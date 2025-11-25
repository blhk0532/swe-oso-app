<?php

namespace Database\Seeders;

use App\Models\PostNummerCheck;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PDO;

class PostNummerCheckSeeder extends Seeder
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
            // Table in SQLite is named `post_nummer` (with underscore)
            $data = $pdo->query('SELECT * FROM post_nummer')->fetchAll(PDO::FETCH_OBJ);

            // Deduplicate by post_nummer to avoid unique constraint issues
            $unique = [];
            foreach ($data as $record) {
                $pn = trim((string) ($record->post_nummer ?? ''));
                if ($pn === '') {
                    continue;
                }
                if (! isset($unique[$pn])) {
                    $unique[$pn] = $record;
                }
            }

            $records = array_values($unique);

            $this->command->info('Starting import of ' . count($records) . ' unique post_nummers records...');

            // Clear existing data and use transactions for better performance
            PostNummerCheck::truncate();

            DB::transaction(function () use ($records) {
                foreach ($records as $record) {
                    PostNummerCheck::create([
                        'id' => $record->id,
                        'post_nummer' => $record->post_nummer,
                        'post_ort' => $record->post_ort,
                        'post_lan' => $record->post_lan,
                    ]);
                }
            });

            $this->command->info('Successfully imported ' . count($records) . ' post_nummer records.');
        } else {
            $this->command->error('SQLite database file not found: ' . $sqlitePath);
        }
    }
}
