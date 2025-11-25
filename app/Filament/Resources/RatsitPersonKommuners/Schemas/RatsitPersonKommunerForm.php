<?php

namespace App\Filament\Resources\RatsitPersonKommuners\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RatsitPersonKommunerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kommun')
                    ->required(),
                TextInput::make('person_count')
                    ->required()
                    ->numeric(),
                TextInput::make('ratsit_link')
                    ->required(),
            ]);
    }
}
