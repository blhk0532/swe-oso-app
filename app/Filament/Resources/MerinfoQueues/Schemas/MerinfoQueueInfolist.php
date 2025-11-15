<?php

namespace App\Filament\Resources\MerinfoQueues\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MerinfoQueueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('post_nummer'),
                TextEntry::make('post_ort'),
                TextEntry::make('post_lan'),
                TextEntry::make('foretag_total')
                    ->numeric(),
                TextEntry::make('personer_total')
                    ->numeric(),
                TextEntry::make('foretag_phone')
                    ->numeric(),
                TextEntry::make('personer_phone')
                    ->numeric(),
                TextEntry::make('foretag_saved')
                    ->numeric(),
                TextEntry::make('personer_saved')
                    ->numeric(),
                TextEntry::make('foretag_queued')
                    ->numeric(),
                TextEntry::make('personer_queued')
                    ->numeric(),
                IconEntry::make('foretag_scraped')
                    ->boolean(),
                IconEntry::make('personer_scraped')
                    ->boolean(),
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
