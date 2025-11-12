<?php

namespace App\Filament\Resources\EniroDatas\Pages;

use App\Filament\Resources\EniroDatas\EniroDatasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEniroData extends EditRecord
{
    protected static string $resource = EniroDatasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
