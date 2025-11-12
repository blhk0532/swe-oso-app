<?php

namespace App\Filament\Resources\RatsitQues\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class RatsitQueForm
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

                                TextInput::make('fodelsedag')
                                    ->label('Date of Birth')
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

                                TextInput::make('civilstand')
                                    ->label('Marital Status')
                                    ->maxLength(255),
                            ])
                            ->columns(2),

                        Section::make('Address Information')
                            ->schema([
                                Textarea::make('gatuadress')
                                    ->label('Street Address')
                                    ->rows(2),

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
                                    ->label('County')
                                    ->maxLength(255),
                            ])
                            ->columns(2),

                        Section::make('Property Information')
                            ->schema([
                                TextInput::make('bostadstyp')
                                    ->label('Housing Type')
                                    ->maxLength(255),

                                TextInput::make('agandeform')
                                    ->label('Form of Ownership')
                                    ->maxLength(255),

                                TextInput::make('boarea')
                                    ->label('Living Area')
                                    ->maxLength(255),

                                TextInput::make('byggar')
                                    ->label('Build Year')
                                    ->maxLength(255),

                                TextInput::make('fastighet')
                                    ->label('Property')
                                    ->maxLength(255),
                            ])
                            ->columns(2),

                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),
            ])
            ->columns(3);
    }
}
