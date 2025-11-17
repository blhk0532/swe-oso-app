<?php

namespace App\Filament\Resources\PersonerData\Pages;

use App\Filament\Resources\PersonerData\PersonerDataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPersonerData extends ListRecords
{
    protected static string $resource = PersonerDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
