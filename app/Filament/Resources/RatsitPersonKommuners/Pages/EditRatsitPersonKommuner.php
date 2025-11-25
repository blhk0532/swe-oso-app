<?php

namespace App\Filament\Resources\RatsitPersonKommuners\Pages;

use App\Filament\Resources\RatsitPersonKommuners\RatsitPersonKommunerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRatsitPersonKommuner extends EditRecord
{
    protected static string $resource = RatsitPersonKommunerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
