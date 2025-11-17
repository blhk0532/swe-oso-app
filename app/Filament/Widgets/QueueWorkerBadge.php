<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Process;

class QueueWorkerBadge extends Widget
{
    protected string $view = 'filament.widgets.queue-worker-badge';

    public function isFilamentWorkerRunning(): bool
    {
        $pgrep = Process::run(['bash', '-lc', 'pgrep -f "artisan queue:work.*filament"']);

        return $pgrep->successful();
    }

    protected function getViewData(): array
    {
        return [
            'running' => $this->isFilamentWorkerRunning(),
        ];
    }
}
