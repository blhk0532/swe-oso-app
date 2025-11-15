<?php

namespace App\Filament\Resources\PostNummerQueues\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PostNummerQueueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('post_nummer'),
            TextEntry::make('post_ort'),
            TextEntry::make('post_lan'),
            TextEntry::make('merinfo_personer_total')->numeric(),
            TextEntry::make('merinfo_foretag_total')->numeric(),
            TextEntry::make('merinfo_personer_saved')->numeric(),
            TextEntry::make('merinfo_foretag_saved')->numeric(),
            TextEntry::make('ratsit_personer_total')->numeric(),
            TextEntry::make('ratsit_foretag_total')->numeric(),
            TextEntry::make('ratsit_personer_saved')->numeric(),
            TextEntry::make('ratsit_foretag_saved')->numeric(),
            TextEntry::make('hitta_personer_total')->numeric(),
            TextEntry::make('hitta_foretag_total')->numeric(),
            TextEntry::make('hitta_personer_saved')->numeric(),
            TextEntry::make('hitta_foretag_saved')->numeric(),
            IconEntry::make('merinfo_queued')->boolean(),
            IconEntry::make('ratsit_queued')->boolean(),
            IconEntry::make('hitta_queued')->boolean(),
            IconEntry::make('post_nummer_queued')->boolean(),
            IconEntry::make('merinfo_scraped')->boolean(),
            IconEntry::make('ratsit_scraped')->boolean(),
            IconEntry::make('hitta_scraped')->boolean(),
            IconEntry::make('post_nummer_scraped')->boolean(),
            IconEntry::make('merinfo_complete')->boolean(),
            IconEntry::make('ratsit_complete')->boolean(),
            IconEntry::make('hitta_complete')->boolean(),
            IconEntry::make('post_nummer_complete')->boolean(),
            IconEntry::make('is_active')->boolean(),
            TextEntry::make('created_at')->dateTime()->placeholder('-'),
            TextEntry::make('updated_at')->dateTime()->placeholder('-'),
        ]);
    }
}
