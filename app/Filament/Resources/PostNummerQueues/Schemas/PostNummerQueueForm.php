<?php

namespace App\Filament\Resources\PostNummerQueues\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostNummerQueueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Location Information')
                ->columns(3)
                ->schema([
                    TextInput::make('post_nummer')->label('Postal Code')->required()->maxLength(10),
                    TextInput::make('post_ort')->label('City')->required()->maxLength(255),
                    TextInput::make('post_lan')->label('Region')->required()->maxLength(255),
                ]),
            Section::make('Merinfo Stats')
                ->columns(2)
                ->schema([
                    TextInput::make('merinfo_personer_total')->label('Merinfo Persons Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('merinfo_foretag_total')->label('Merinfo Företag Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('merinfo_personer_saved')->label('Merinfo Persons Saved')->numeric()->default(0)->minValue(0),
                    TextInput::make('merinfo_foretag_saved')->label('Merinfo Företag Saved')->numeric()->default(0)->minValue(0),
                    Toggle::make('merinfo_queued')->label('Merinfo Queued'),
                    Toggle::make('merinfo_scraped')->label('Merinfo Scraped'),
                ]),
            Section::make('Ratsit Stats')
                ->columns(2)
                ->schema([
                    TextInput::make('ratsit_personer_total')->label('Ratsit Persons Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('ratsit_foretag_total')->label('Ratsit Företag Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('ratsit_personer_saved')->label('Ratsit Persons Saved')->numeric()->default(0)->minValue(0),
                    TextInput::make('ratsit_foretag_saved')->label('Ratsit Företag Saved')->numeric()->default(0)->minValue(0),
                    Toggle::make('ratsit_queued')->label('Ratsit Queued'),
                    Toggle::make('ratsit_scraped')->label('Ratsit Scraped'),
                ]),
            Section::make('Hitta Stats')
                ->columns(2)
                ->schema([
                    TextInput::make('hitta_personer_total')->label('Hitta Persons Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('hitta_foretag_total')->label('Hitta Företag Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('hitta_personer_saved')->label('Hitta Persons Saved')->numeric()->default(0)->minValue(0),
                    TextInput::make('hitta_foretag_saved')->label('Hitta Företag Saved')->numeric()->default(0)->minValue(0),
                    Toggle::make('hitta_queued')->label('Hitta Queued'),
                    Toggle::make('hitta_scraped')->label('Hitta Scraped'),
                ]),
            Section::make('Postnummer Internal Stats')
                ->columns(2)
                ->schema([
                    TextInput::make('post_nummer_personer_total')->label('PN Persons Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('post_nummer_foretag_total')->label('PN Företag Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('post_nummer_personer_saved')->label('PN Persons Saved')->numeric()->default(0)->minValue(0),
                    TextInput::make('post_nummer_foretag_saved')->label('PN Företag Saved')->numeric()->default(0)->minValue(0),
                    Toggle::make('post_nummer_queued')->label('PN Queued'),
                    Toggle::make('post_nummer_scraped')->label('PN Scraped'),
                ]),
            Section::make('Status Flags')
                ->columns(4)
                ->schema([
                    Toggle::make('merinfo_checked')->label('Merinfo Checked'),
                    Toggle::make('ratsit_checked')->label('Ratsit Checked'),
                    Toggle::make('hitta_checked')->label('Hitta Checked'),
                    Toggle::make('post_nummer_checked')->label('PN Checked'),
                    Toggle::make('merinfo_complete')->label('Merinfo Complete'),
                    Toggle::make('ratsit_complete')->label('Ratsit Complete'),
                    Toggle::make('hitta_complete')->label('Hitta Complete'),
                    Toggle::make('post_nummer_complete')->label('PN Complete'),
                    Toggle::make('is_active')->label('Active')->default(true),
                ]),
        ]);
    }
}
