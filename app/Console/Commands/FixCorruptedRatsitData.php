<?php

namespace App\Console\Commands;

use App\Models\RatsitData;
use Illuminate\Console\Command;

class FixCorruptedRatsitData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-corrupted-ratsit-data {--delete : Delete corrupted records instead of fixing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix or remove RatsitData records with corrupted array data containing "[object Object]"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $arrayFields = ['personer', 'foretag', 'grannar', 'fordon', 'hundar', 'bolagsengagemang'];

        $query = RatsitData::query();

        foreach ($arrayFields as $field) {
            $query->orWhere(function ($q) use ($field) {
                $q->whereNotNull($field)
                    ->where($field, 'like', '%[object Object]%');
            });
        }

        $corruptedRecords = $query->get();

        if ($corruptedRecords->isEmpty()) {
            $this->info('No corrupted records found.');

            return;
        }

        $this->info("Found {$corruptedRecords->count()} corrupted record(s).");

        if ($this->option('delete')) {
            $this->warn('Deleting corrupted records...');
            $corruptedRecords->each->delete();
            $this->info('Corrupted records deleted.');
        } else {
            $this->warn('Fixing corrupted records by resetting array fields...');
            foreach ($corruptedRecords as $record) {
                foreach ($arrayFields as $field) {
                    if (is_array($record->$field) && in_array('[object Object]', $record->$field)) {
                        $record->$field = [];
                    }
                }
                $record->save();
            }
            $this->info('Corrupted records fixed.');
        }
    }
}
