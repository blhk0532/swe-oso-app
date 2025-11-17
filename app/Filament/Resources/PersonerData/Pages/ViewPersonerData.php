<?php

namespace App\Filament\Resources\PersonerData\Pages;

use App\Filament\Resources\PersonerData\PersonerDataResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPersonerData extends ViewRecord
{
    protected static string $resource = PersonerDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
