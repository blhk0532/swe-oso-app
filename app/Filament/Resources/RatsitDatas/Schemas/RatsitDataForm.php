<?php

namespace App\Filament\Resources\RatsitDatas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RatsitDataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Person Information')
                            ->schema([
                                TextInput::make('personnamn')
                                    ->label('Full Name')
                                    ->maxLength(255),

                                TextInput::make('fornamn')
                                    ->label('First Name')
                                    ->maxLength(255),

                                TextInput::make('efternamn')
                                    ->label('Last Name')
                                    ->maxLength(255),

                                TextInput::make('personnummer')
                                    ->label('Social Security Number')
                                    ->maxLength(255),

                                DatePicker::make('fodelsedag')
                                    ->label('Date of Birth')
                                    ->displayFormat('Y-m-d'),

                                TextInput::make('alder')
                                    ->label('Age')
                                    ->maxLength(255),

                                Select::make('kon')
                                    ->label('Sex')
                                    ->options([
                                        'M' => 'Male',
                                        'F' => 'Female',
                                        'O' => 'Other',
                                    ]),

                                Select::make('civilstand')
                                    ->label('Marital Status')
                                    ->options([
                                        'single' => 'Single',
                                        'married' => 'Married',
                                        'divorced' => 'Divorced',
                                        'widowed' => 'Widowed',
                                    ]),

                                TextInput::make('telefon')
                                    ->label('Phone Number')
                                    ->maxLength(255),

                                Repeater::make('telfonnummer')
                                    ->label('Additional Phone Numbers')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Phone Number')
                                            ->required(),
                                    ])
                                    ->default([])
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $decoded);
                                            }
                                            $parts = array_filter(array_map('trim', explode('|', $state)));

                                            return array_map(fn ($v) => ['value' => $v], $parts);
                                        }

                                        return [];
                                    })
                                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('value')->filter()->values()->all())
                                    ->collapsible()
                                    ->itemLabel(fn ($state): ?string => is_array($state) ? ($state['value'] ?? null) : (is_string($state) ? $state : null)),

                                Repeater::make('epost_adress')
                                    ->label('Email Addresses')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Email')
                                            ->email()
                                            ->required(),
                                    ])
                                    ->default([])
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $decoded);
                                            }
                                            $parts = array_filter(array_map('trim', explode('|', $state)));

                                            return array_map(fn ($v) => ['value' => $v], $parts);
                                        }

                                        return [];
                                    })
                                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('value')->filter()->values()->all())
                                    ->collapsible()
                                    ->itemLabel(fn ($state): ?string => is_array($state) ? ($state['value'] ?? null) : (is_string($state) ? $state : null)),

                                Repeater::make('bolagsengagemang')
                                    ->label('Corporate Commitments')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Engagement')
                                            ->required(),
                                    ])
                                    ->default([])
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $decoded);
                                            }
                                            $parts = array_filter(array_map('trim', explode('|', $state)));

                                            return array_map(fn ($v) => ['value' => $v], $parts);
                                        }

                                        return [];
                                    })
                                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('value')->filter()->values()->all())
                                    ->collapsible()
                                    ->itemLabel(fn ($state): ?string => is_array($state) ? ($state['value'] ?? null) : (is_string($state) ? $state : null)),
                            ])
                            ->columns(2)
                            ->collapsible(),

                        Section::make('Address Information')
                            ->schema([
                                Textarea::make('gatuadress')
                                    ->label('Street Address')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                TextInput::make('postnummer')
                                    ->label('Postal Code')
                                    ->maxLength(255),

                                TextInput::make('postort')
                                    ->label('City')
                                    ->maxLength(255),

                                TextInput::make('forsamling')
                                    ->label('Parish')
                                    ->maxLength(255),

                                TextInput::make('kommun')
                                    ->label('Municipality')
                                    ->maxLength(255),

                                TextInput::make('lan')
                                    ->label('State')
                                    ->maxLength(255),

                                TextInput::make('fastighet')
                                    ->label('Fastighetsbetäckning')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->step(0.0000001),

                                TextInput::make('latitud')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->step(0.0000001),
                            ])
                            ->columns(2)
                            ->collapsible(),

                        Section::make('Address Property Details')
                            ->schema([
                                TextInput::make('agandeform')
                                    ->label('Form of Ownership')
                                    ->maxLength(255),

                                TextInput::make('bostadstyp')
                                    ->label('Housing Type')
                                    ->maxLength(255),

                                TextInput::make('boarea')
                                    ->label('Living Area')
                                    ->maxLength(255),

                                TextInput::make('byggar')
                                    ->label('Year of Construction')
                                    ->maxLength(255),

                                Repeater::make('personer')
                                    ->label('Persons at Address')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Name')
                                            ->required(),
                                    ])
                                    ->default([])
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $decoded);
                                            }
                                            $parts = array_filter(array_map('trim', explode('|', $state)));

                                            return array_map(fn ($v) => ['value' => $v], $parts);
                                        }

                                        return [];
                                    })
                                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('value')->filter()->values()->all())
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->itemLabel(fn ($state): ?string => is_array($state) ? ($state['value'] ?? null) : (is_string($state) ? $state : null)),

                                Repeater::make('foretag')
                                    ->label('Companies at Address')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Company Name')
                                            ->required(),
                                    ])
                                    ->default([])
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $decoded);
                                            }
                                            $parts = array_filter(array_map('trim', explode('|', $state)));

                                            return array_map(fn ($v) => ['value' => $v], $parts);
                                        }

                                        return [];
                                    })
                                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('value')->filter()->values()->all())
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->itemLabel(fn ($state): ?string => is_array($state) ? ($state['value'] ?? null) : (is_string($state) ? $state : null)),

                                Repeater::make('grannar')
                                    ->label('Neighbors')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Name')
                                            ->required(),
                                    ])
                                    ->default([])
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $decoded);
                                            }
                                            $parts = array_filter(array_map('trim', explode('|', $state)));

                                            return array_map(fn ($v) => ['value' => $v], $parts);
                                        }

                                        return [];
                                    })
                                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('value')->filter()->values()->all())
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->itemLabel(fn ($state): ?string => is_array($state) ? ($state['value'] ?? null) : (is_string($state) ? $state : null)),

                                Repeater::make('fordon')
                                    ->label('Vehicles')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Vehicle')
                                            ->required(),
                                    ])
                                    ->default([])
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $decoded);
                                            }
                                            $parts = array_filter(array_map('trim', explode('|', $state)));

                                            return array_map(fn ($v) => ['value' => $v], $parts);
                                        }

                                        return [];
                                    })
                                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('value')->filter()->values()->all())
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->itemLabel(fn ($state): ?string => is_array($state) ? ($state['value'] ?? null) : (is_string($state) ? $state : null)),

                                Repeater::make('hundar')
                                    ->label('Dogs')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Dog Name')
                                            ->required(),
                                    ])
                                    ->default([])
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $state);
                                        }
                                        if (is_string($state)) {
                                            $decoded = json_decode($state, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                return array_map(fn ($v) => ['value' => is_string($v) ? $v : (string) $v], $decoded);
                                            }
                                            $parts = array_filter(array_map('trim', explode('|', $state)));

                                            return array_map(fn ($v) => ['value' => $v], $parts);
                                        }

                                        return [];
                                    })
                                    ->dehydrateStateUsing(fn ($state) => collect($state)->pluck('value')->filter()->values()->all())
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->itemLabel(fn ($state): ?string => is_array($state) ? ($state['value'] ?? null) : (is_string($state) ? $state : null)),
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->helperText('Whether this record is active'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
