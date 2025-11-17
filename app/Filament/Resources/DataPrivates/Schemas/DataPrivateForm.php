<?php

namespace App\Filament\Resources\DataPrivates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DataPrivateForm
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

                                Repeater::make('telefon')
                                    ->label('Phone Numbers')
                                    ->schema([
                                        TextInput::make('number')
                                            ->label('Phone Number')
                                            ->required(),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['number'] ?? null),

                                Repeater::make('epost_adress')
                                    ->label('Email Addresses')
                                    ->schema([
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required(),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['email'] ?? null),

                                Repeater::make('bolagsengagemang')
                                    ->label('Corporate Commitments')
                                    ->schema([
                                        TextInput::make('company')
                                            ->label('Company')
                                            ->required(),
                                        TextInput::make('role')
                                            ->label('Role')
                                            ->maxLength(255),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->columns(2),
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
                                Select::make('agandeform')
                                    ->label('Form of Ownership')
                                    ->maxLength(255),

                                Select::make('bostadstyp')
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
                                        TextInput::make('name')
                                            ->label('Name')
                                            ->required(),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->columnSpanFull(),

                                Repeater::make('foretag')
                                    ->label('Companies at Address')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Company Name')
                                            ->required(),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->columnSpanFull(),

                                Repeater::make('grannar')
                                    ->label('Neighbors')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Name')
                                            ->required(),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->columnSpanFull(),

                                Repeater::make('fordon')
                                    ->label('Vehicles')
                                    ->schema([
                                        TextInput::make('type')
                                            ->label('Vehicle Type')
                                            ->required(),
                                        TextInput::make('registration')
                                            ->label('Registration')
                                            ->maxLength(255),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->columns(2)
                                    ->columnSpanFull(),

                                Repeater::make('hundar')
                                    ->label('Dogs')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Dog Name')
                                            ->required(),
                                    ])
                                    ->defaultItems(0)
                                    ->collapsible()
                                    ->columnSpanFull(),
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
