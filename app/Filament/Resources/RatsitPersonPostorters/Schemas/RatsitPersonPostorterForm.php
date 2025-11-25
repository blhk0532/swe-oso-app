<?php

namespace App\Filament\Resources\RatsitPersonPostorters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RatsitPersonPostorterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('post_ort')
                    ->required(),
                TextInput::make('post_nummer')
                    ->required(),
                TextInput::make('person_count')
                    ->required()
                    ->numeric(),
                TextInput::make('ratsit_link')
                    ->required(),
            ]);
    }
}
