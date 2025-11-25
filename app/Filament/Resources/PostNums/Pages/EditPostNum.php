<?php

namespace App\Filament\Resources\PostNums\Pages;

use App\Filament\Resources\PostNums\PostNumResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPostNum extends EditRecord
{
    protected static string $resource = PostNumResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
