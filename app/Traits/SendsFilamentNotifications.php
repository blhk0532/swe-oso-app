<?php

namespace App\Traits;

use App\Models\User;
use Filament\Notifications\Notification;

trait SendsFilamentNotifications
{
    /**
     * Send a success notification to all admin users
     */
    protected function sendSuccessNotification(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->success()
            ->title($title)
            ->body($body)
            ->icon('heroicon-o-check-circle')
            ->iconColor('success');

        $this->sendToAdmins($notification);
    }

    /**
     * Send an error notification to all admin users
     */
    protected function sendErrorNotification(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->danger()
            ->title($title)
            ->body($body)
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->persistent();

        $this->sendToAdmins($notification);
    }

    /**
     * Send an info notification to all admin users
     */
    protected function sendInfoNotification(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->info()
            ->title($title)
            ->body($body)
            ->icon('heroicon-o-information-circle')
            ->iconColor('info');

        $this->sendToAdmins($notification);
    }

    /**
     * Send a warning notification to all admin users
     */
    protected function sendWarningNotification(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->warning()
            ->title($title)
            ->body($body)
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('warning');

        $this->sendToAdmins($notification);
    }

    /**
     * Send notification to all admin users or specific user
     */
    protected function sendToAdmins(Notification $notification, ?User $specificUser = null): void
    {
        if ($specificUser) {
            $notification->sendToDatabase($specificUser);

            return;
        }

        // Send to all users (or you can filter by admin role if you have roles)
        $users = User::all();

        foreach ($users as $user) {
            $notification->sendToDatabase($user);
        }
    }

    /**
     * Send a job completed notification
     */
    protected function sendJobCompletedNotification(string $jobName, array $details = []): void
    {
        $body = 'Job completed successfully';

        if (! empty($details)) {
            $body .= ': ' . implode(', ', array_map(
                fn ($key, $value) => "{$key}: {$value}",
                array_keys($details),
                $details
            ));
        }

        $this->sendSuccessNotification(
            title: "Job Completed: {$jobName}",
            body: $body
        );
    }

    /**
     * Send a job failed notification
     */
    protected function sendJobFailedNotification(string $jobName, string $error, array $details = []): void
    {
        $body = "Error: {$error}";

        if (! empty($details)) {
            $body .= ' | ' . implode(', ', array_map(
                fn ($key, $value) => "{$key}: {$value}",
                array_keys($details),
                $details
            ));
        }

        $this->sendErrorNotification(
            title: "Job Failed: {$jobName}",
            body: $body
        );
    }
}
