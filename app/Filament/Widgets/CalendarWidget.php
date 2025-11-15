<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Task::class;

    public function fetchEvents(array $fetchInfo): array
    {
        return Task::where('start', '>=', $fetchInfo['start'])
            ->where('end', '<=', $fetchInfo['end'])
            ->get()
            ->map(function (Task $task) {
                return [
                    'id' => $task->id,
                    'title' => $task->name,
                    'start' => $task->start,
                    'end' => $task->end,
                ];
            })
            ->toArray();
    }

    public static function canView(): bool
    {
        // Allow viewing the calendar widget. Change this to a permission check
        // if you want to restrict access (e.g. auth()->user()->can('view calendar')).
        return true;
    }
}
