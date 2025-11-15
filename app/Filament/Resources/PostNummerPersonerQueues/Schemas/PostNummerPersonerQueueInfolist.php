<?php

namespace App\Filament\Resources\PostNummerPersonerQueues\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PostNummerPersonerQueueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('post_nummer'),
            TextEntry::make('post_ort'),
            TextEntry::make('post_lan'),
            TextEntry::make('merinfo_personer_total')->numeric(),
            TextEntry::make('merinfo_personer_saved')->numeric(),
            TextEntry::make('merinfo_status'),
            TextEntry::make('ratsit_personer_total')->numeric(),
            TextEntry::make('ratsit_personer_saved')->numeric(),
            TextEntry::make('ratsit_status'),
            TextEntry::make('hitta_personer_total')->numeric(),
            TextEntry::make('hitta_personer_saved')->numeric(),
            TextEntry::make('hitta_status'),
            TextEntry::make('post_nummer_personer_total')->numeric(),
            TextEntry::make('post_nummer_personer_saved')->numeric(),
            TextEntry::make('post_nummer_status'),
            IconEntry::make('is_active')->boolean(),
            TextEntry::make('created_at')->dateTime()->placeholder('-'),
            TextEntry::make('updated_at')->dateTime()->placeholder('-'),
        ]);
    }
}
