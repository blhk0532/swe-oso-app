<?php

namespace App\Filament\Resources\RatsitForetagKommuners\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RatsitForetagKommunerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kommun')
                    ->required(),
                TextInput::make('foretag_count')
                    ->required()
                    ->numeric(),
                TextInput::make('ratsit_link')
                    ->required(),
            ]);
    }
}
