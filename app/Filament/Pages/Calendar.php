<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CalendarWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Widgets\Widget;
use UnitEnum;

class Calendar extends Page
{
    protected string $view = 'filament.pages.calendar';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Kalender Översikt';

    protected static string | UnitEnum | null $navigationGroup = 'ADMINISTRATION';

    /**
     * Return header widgets for the page so Filament will render them
     * in the page header area (the framework filters by canView()).
     *
     * @return array<class-string<Widget>>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::class,
        ];
    }
}
