<?php

namespace App\Filament\Resources\Jobs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class JobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('queue'),
                TextEntry::make('payload')
                    ->columnSpanFull(),
                TextEntry::make('attempts')
                    ->numeric(),
                TextEntry::make('reserved_at')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('available_at')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->numeric(),
            ]);
    }
}
