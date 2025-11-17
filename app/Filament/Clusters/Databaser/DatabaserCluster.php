<?php

namespace App\Filament\Clusters\Databaser;

use BackedEnum;
use Filament\Clusters\Cluster;
use UnitEnum;

class DatabaserCluster extends Cluster
{
    // Top-level icon for the cluster — use an existing heroicon name
    // 'heroicon-o-database' does not exist in the Heroicons set; use a stack/collection icon instead
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    // Place the cluster inside a higher-level navigation group if desired
    protected static UnitEnum | string | null $navigationGroup = 'Databaser';

    protected static ?int $navigationSort = 1;

    // Base slug used for nested resource slugs
    protected static ?string $slug = 'databaser';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
