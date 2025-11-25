<?php

namespace App\Filament\Resources\RatsitPersonPostorters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RatsitPersonPostorterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('post_ort'),
                TextEntry::make('post_nummer'),
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
