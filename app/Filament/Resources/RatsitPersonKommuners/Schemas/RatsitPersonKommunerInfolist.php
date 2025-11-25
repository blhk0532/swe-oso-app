<?php

namespace App\Filament\Resources\RatsitPersonKommuners\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RatsitPersonKommunerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('kommun'),
                TextEntry::make('person_count')
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
