<?php

namespace App\Filament\Resources\PostNums\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PostNumInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('post_nummer'),
                TextEntry::make('post_ort'),
                TextEntry::make('post_lan'),
                TextEntry::make('merinfo_personer_total')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('merinfo_foretag_total')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('hitta_personer_total')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('hitta_foretag_total')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ratsit_personer_total')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ratsit_foretag_total')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
