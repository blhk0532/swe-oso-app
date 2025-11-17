<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Traits\SendsFilamentNotifications;
use Illuminate\Console\Command;

class TestNotificationCommand extends Command
{
    use SendsFilamentNotifications;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Filament notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Filament notifications...');

        // Test success notification
        $this->sendSuccessNotification(
            title: 'Test Success',
            body: 'This is a test success notification'
        );

        $this->info('✓ Success notification sent');

        // Test error notification
        $this->sendErrorNotification(
            title: 'Test Error',
            body: 'This is a test error notification'
        );

        $this->info('✓ Error notification sent');

        // Test job completed notification
        $this->sendJobCompletedNotification(
            jobName: 'Test Job',
            details: ['Status' => 'Completed', 'Duration' => '5s']
        );

        $this->info('✓ Job completed notification sent');

        $userCount = User::count();
        $this->info("Notifications sent to {$userCount} user(s)");

        return self::SUCCESS;
    }
}
