<?php

namespace App\Filament\Resources\UpplysningDatas\Pages;

use App\Filament\Resources\UpplysningDatas\UpplysningDatasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUpplysningData extends EditRecord
{
    protected static string $resource = UpplysningDatasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
