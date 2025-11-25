<?php

namespace App\Filament\Resources\RatsitForetagAdressers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RatsitForetagAdresserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('post_ort')
                    ->required(),
                TextInput::make('post_nummer')
                    ->required(),
                TextInput::make('gatuadress_namn')
                    ->required(),
                TextInput::make('foretag_count')
                    ->required()
                    ->numeric(),
                TextInput::make('ratsit_link')
                    ->required(),
            ]);
    }
}
