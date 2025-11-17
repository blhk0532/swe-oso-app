<?php

namespace Database\Seeders;

use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\ProgressBar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::raw('SET time_zone=\'+00:00\'');

        // Clear images
        Storage::deleteDirectory('public');

        // Admin
        $this->command->warn(PHP_EOL . 'Creating admin user...');
        $user = $this->withProgressBar(1, fn () => User::factory(1)->create([
            'name' => 'a',
            'email' => 'a@a.a',
            'password' => Hash::make('a'),
        ]));
        $this->command->info('Admin user created.');

        // Queues
        $this->command->warn(PHP_EOL . 'Seeding queue tables...');
        $this->call([
            // Base postnummer
            PostNummerSeeder::class,

            // Unified queues
            // MerinfoQueueSeeder::class,
            // HittaQueueSeeder::class,
            // RatsitQueueSeeder::class,

            // Per-type queues

            //    MerinfoForetagQueueSeeder::class,
            //    MerinfoPersonerQueueSeeder::class,
            //    HittaForetagQueueSeeder::class,
            //    HittaPersonerQueueSeeder::class,
            //    RatsitForetagQueueSeeder::class,
            //    RatsitPersonerQueueSeeder::class,

            // Postnummer summary queue tables

            // PostNummerQueueSeeder::class,
            //    PostNummerForetagQueueSeeder::class,
            //    PostNummerPersonerQueueSeeder::class,
        ]);

    }

    protected function withProgressBar(int $amount, Closure $createCollectionOfOne): Collection
    {
        $progressBar = new ProgressBar($this->command->getOutput(), $amount);

        $progressBar->start();

        $items = new Collection;

        foreach (range(1, $amount) as $i) {
            $items = $items->merge(
                $createCollectionOfOne()
            );
            $progressBar->advance();
        }

        $progressBar->finish();

        $this->command->getOutput()->writeln('');

        return $items;
    }
}
