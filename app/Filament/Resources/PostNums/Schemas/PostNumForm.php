<?php

namespace App\Filament\Resources\PostNums\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PostNumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('post_nummer')
                    ->required(),
                TextInput::make('post_ort')
                    ->required(),
                TextInput::make('post_lan')
                    ->required(),
                TextInput::make('merinfo_personer_total')
                    ->numeric()
                    ->default(0),
                TextInput::make('merinfo_foretag_total')
                    ->numeric()
                    ->default(0),
                TextInput::make('hitta_personer_total')
                    ->numeric()
                    ->default(0),
                TextInput::make('hitta_foretag_total')
                    ->numeric()
                    ->default(0),
                TextInput::make('ratsit_personer_total')
                    ->numeric()
                    ->default(0),
                TextInput::make('ratsit_foretag_total')
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->default('idle'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
