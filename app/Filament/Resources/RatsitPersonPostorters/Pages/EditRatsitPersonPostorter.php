<?php

namespace App\Filament\Resources\RatsitPersonPostorters\Pages;

use App\Filament\Resources\RatsitPersonPostorters\RatsitPersonPostorterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRatsitPersonPostorter extends EditRecord
{
    protected static string $resource = RatsitPersonPostorterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
