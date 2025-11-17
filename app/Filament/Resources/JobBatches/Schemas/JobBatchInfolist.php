<?php

namespace App\Filament\Resources\JobBatches\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class JobBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('name'),
                TextEntry::make('total_jobs')
                    ->numeric(),
                TextEntry::make('pending_jobs')
                    ->numeric(),
                TextEntry::make('failed_jobs')
                    ->numeric(),
                TextEntry::make('failed_job_ids')
                    ->columnSpanFull(),
                TextEntry::make('options')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('cancelled_at')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->numeric(),
                TextEntry::make('finished_at')
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}
