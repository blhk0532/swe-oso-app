<?php

namespace App\Filament\Resources\HittaSes\Pages;

use App\Filament\Resources\HittaSes\HittaSeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHittaSe extends EditRecord
{
    protected static string $resource = HittaSeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
