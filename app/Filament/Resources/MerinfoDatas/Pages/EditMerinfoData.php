<?php

namespace App\Filament\Resources\MerinfoDatas\Pages;

use App\Filament\Resources\MerinfoDatas\MerinfoDataResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMerinfoData extends EditRecord
{
    protected static string $resource = MerinfoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
