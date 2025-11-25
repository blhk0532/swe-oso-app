<?php

namespace App\Filament\Resources\RatsitForetagPostorters\Pages;

use App\Filament\Resources\RatsitForetagPostorters\RatsitForetagPostorterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRatsitForetagPostorter extends EditRecord
{
    protected static string $resource = RatsitForetagPostorterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
