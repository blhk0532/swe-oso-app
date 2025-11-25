<?php

namespace App\Filament\Resources\RatsitForetagKommuners\Pages;

use App\Filament\Resources\RatsitForetagKommuners\RatsitForetagKommunerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRatsitForetagKommuner extends EditRecord
{
    protected static string $resource = RatsitForetagKommunerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
