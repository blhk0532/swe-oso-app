<?php

namespace App\Filament\Resources\PostNummerPersonerQueues\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostNummerPersonerQueueForm
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
            Section::make('Merinfo Persons')
                ->columns(2)
                ->schema([
                    TextInput::make('merinfo_personer_total')->label('Merinfo Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('merinfo_personer_saved')->label('Merinfo Saved')->numeric()->default(0)->minValue(0),
                    TextInput::make('merinfo_status')->label('Merinfo Status')->maxLength(50),
                ]),
            Section::make('Ratsit Persons')
                ->columns(2)
                ->schema([
                    TextInput::make('ratsit_personer_total')->label('Ratsit Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('ratsit_personer_saved')->label('Ratsit Saved')->numeric()->default(0)->minValue(0),
                    TextInput::make('ratsit_status')->label('Ratsit Status')->maxLength(50),
                ]),
            Section::make('Hitta Persons')
                ->columns(2)
                ->schema([
                    TextInput::make('hitta_personer_total')->label('Hitta Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('hitta_personer_saved')->label('Hitta Saved')->numeric()->default(0)->minValue(0),
                    TextInput::make('hitta_status')->label('Hitta Status')->maxLength(50),
                ]),
            Section::make('Postnummer Internal')
                ->columns(2)
                ->schema([
                    TextInput::make('post_nummer_personer_total')->label('Postnummer Total')->numeric()->default(0)->minValue(0),
                    TextInput::make('post_nummer_personer_saved')->label('Postnummer Saved')->numeric()->default(0)->minValue(0),
                    TextInput::make('post_nummer_status')->label('Postnummer Status')->maxLength(50),
                ]),
            Section::make('Status')
                ->columns(1)
                ->schema([
                    Toggle::make('is_active')->label('Active')->default(true),
                ]),
        ]);
    }
}
