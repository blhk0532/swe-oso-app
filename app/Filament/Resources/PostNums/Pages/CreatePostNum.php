<?php

namespace App\Filament\Resources\PostNums\Pages;

use App\Filament\Resources\PostNums\PostNumResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostNum extends CreateRecord
{
    protected static string $resource = PostNumResource::class;
}
