<?php

namespace App\Filament\Resources\MerinfoPersonerQueues\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MerinfoPersonerQueueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('post_nummer'),
            TextEntry::make('post_ort'),
            TextEntry::make('post_lan'),
            TextEntry::make('personer_total')->numeric(),
            TextEntry::make('personer_saved')->numeric(),
            TextEntry::make('personer_phone')->numeric(),
            TextEntry::make('personer_house')->numeric(),
            TextEntry::make('personer_page')->numeric(),
            TextEntry::make('personer_pages')->numeric(),
            TextEntry::make('personer_status'),
            IconEntry::make('personer_queued')->boolean(),
            IconEntry::make('personer_scraped')->boolean(),
            IconEntry::make('is_active')->boolean(),
            TextEntry::make('created_at')->dateTime()->placeholder('-'),
            TextEntry::make('updated_at')->dateTime()->placeholder('-'),
        ]);
    }
}
