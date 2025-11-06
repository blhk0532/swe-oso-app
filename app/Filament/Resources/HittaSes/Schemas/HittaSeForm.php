<?php

namespace App\Filament\Resources\HittaSes\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HittaSeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->schema([
                        TextInput::make('personnamn')
                            ->label('Name')
                            ->maxLength(255),

                        TextInput::make('alder')
                            ->label('Age')
                            ->maxLength(255),

                        Select::make('kon')
                            ->label('Sex')
                            ->options([
                                'Man' => 'Man',
                                'Kvinna' => 'Kvinna',
                            ]),
                    ])
                    ->columns(3),

                Section::make('Address Information')
                    ->schema([
                        TextInput::make('gatuadress')
                            ->label('Street Address')
                            ->maxLength(255),

                        TextInput::make('postnummer')
                            ->label('Zip Code')
                            ->maxLength(255),

                        TextInput::make('postort')
                            ->label('City')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('telefon')
                            ->label('Phone')
                            ->maxLength(255),

                        Textarea::make('karta')
                            ->label('Map URL')
                            ->rows(2),

                        Textarea::make('link')
                            ->label('Profile URL')
                            ->rows(2),
                    ])
                    ->columns(1),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Toggle::make('is_telefon')
                            ->label('Has Phone')
                            ->default(false),

                        Toggle::make('is_ratsit')
                            ->label('In Ratsit')
                            ->default(false),
                    ])
                    ->columns(3),
            ]);
    }
}
