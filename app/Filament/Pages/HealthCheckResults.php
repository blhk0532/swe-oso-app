<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\ChipIcon;
use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

class HealthCheckResults extends BaseHealthCheckResults
{
    protected static string | BackedEnum | null $navigationIcon = ChipIcon::class;

    public static function shouldRegisterNavigation(): bool
    {
        // Hide Shop > Customers from sidebar navigation.
        return true;
    }
}
