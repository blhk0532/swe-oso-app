<?php

namespace App\Support;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Process;

class QueueAutostart
{
    /**
     * Attempt to autostart a named queue worker if configured and not running.
     *
     * @param  bool  $notify  Whether to show a Filament notification when autostarted
     * @return bool true if a worker was started now, false otherwise
     */
    public static function attempt(string $queueName, bool $notify = true): bool
    {
        if (! config('queue_autostart.enabled')) {
            return false;
        }

        $queues = config('queue_autostart.queues', []);
        if (! in_array($queueName, $queues, true)) {
            return false;
        }

        $pgrep = Process::run(['bash', '-lc', "pgrep -f \"artisan queue:work.*{$queueName}\""]);
        if ($pgrep->successful()) {
            return false;
        }

        $cmd = 'php ' . base_path('artisan') . " queue:work database --queue={$queueName} --tries=3 --timeout=0 --stop-when-empty > /dev/null 2>&1 &";
        shell_exec($cmd);

        if ($notify) {
            Notification::make()
                ->title(ucfirst($queueName) . ' Worker Autostarted')
                ->body('No ' . $queueName . ' worker was running — a worker was autostarted to process queued jobs.')
                ->warning()
                ->send();
        }

        return true;
    }
}
