<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

class UserAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        if ($record->avatar_url) {
            return $record->avatar_url;
        }

        // Fallback to UiAvatars
        $name = $record->name ?? 'User';

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=FFFFFF&background=gray';
    }
}
