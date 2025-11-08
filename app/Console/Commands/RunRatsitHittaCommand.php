<?php

namespace App\Console\Commands;

use App\Jobs\ProcessPostNummer;
use App\Models\PostNummer;
use Illuminate\Console\Command;

class RunRatsitHittaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratsit:hitta {post_nummer* : One or more post nummer to search for} {--sync : Run synchronously instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue ratsit_hitta.mjs script for a specific post nummer';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var array<int, string> $postNummers */
        $postNummers = (array) $this->argument('post_nummer');
        $sync = $this->option('sync');

        $dispatched = 0;

        foreach ($postNummers as $postNummer) {
            $postNummer = trim((string) $postNummer);

            if ($postNummer === '') {
                continue;
            }

            $record = PostNummer::where('post_nummer', $postNummer)->first();

            if (! $record) {
                $this->error("Post nummer {$postNummer} not found");

                continue;
            }

            if ($record->status === 'running') {
                $this->warn("Post nummer {$postNummer} is already running");

                continue;
            }

            if ($sync) {
                $this->info("Running synchronously for post nummer: {$postNummer}");
                ProcessPostNummer::dispatchSync($postNummer);
                $this->info('Job completed');
                $dispatched++;
            } else {
                $this->info("Queuing job for post nummer: {$postNummer}");
                ProcessPostNummer::dispatch($postNummer);
                $dispatched++;
            }
        }

        if ($dispatched === 0) {
            $this->warn('No jobs dispatched.');

            return self::FAILURE;
        }

        if (! $sync) {
            $this->info("Queued {$dispatched} job(s) successfully");
        }

        return self::SUCCESS;
    }
}
