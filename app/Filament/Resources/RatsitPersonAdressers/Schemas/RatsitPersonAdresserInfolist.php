<?php

namespace App\Filament\Resources\RatsitPersonAdressers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RatsitPersonAdresserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('post_ort'),
                TextEntry::make('post_nummer'),
                TextEntry::make('gatuadress_namn'),
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
