<?php

namespace App\Filament\Resources\HittaQueues\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HittaQueueForm
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
            Section::make('Statistics')
                ->columns(3)
                ->schema([
                    TextInput::make('foretag_total')->label('Total Companies')->numeric()->default(0)->minValue(0),
                    TextInput::make('personer_total')->label('Total Persons')->numeric()->default(0)->minValue(0),
                    TextInput::make('personer_house')->label('Persons House')->numeric()->default(0)->minValue(0),
                    TextInput::make('foretag_phone')->label('Companies Phone')->numeric()->default(0)->minValue(0),
                    TextInput::make('personer_phone')->label('Persons Phone')->numeric()->default(0)->minValue(0),
                ]),
            Section::make('Progress Tracking')
                ->columns(2)
                ->schema([
                    TextInput::make('foretag_saved')->label('Companies Saved')->numeric()->default(0)->minValue(0),
                    TextInput::make('personer_saved')->label('Persons Saved')->numeric()->default(0)->minValue(0),
                    Toggle::make('foretag_queued')->label('Companies Queued'),
                    Toggle::make('personer_queued')->label('Persons Queued'),
                ]),
            Section::make('Status')
                ->columns(3)
                ->schema([
                    Toggle::make('foretag_scraped')->label('Companies Scraped'),
                    Toggle::make('personer_scraped')->label('Persons Scraped'),
                    Toggle::make('is_active')->label('Active')->default(true),
                ]),
        ]);
    }
}
