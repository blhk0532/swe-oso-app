<?php

namespace App\Filament\Resources\RatsitForetagKommuners\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RatsitForetagKommunerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kommun'),
                TextEntry::make('foretag_count')
                    ->numeric(),
                TextEntry::make('ratsit_link'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
