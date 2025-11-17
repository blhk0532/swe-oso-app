<?php

namespace App\Jobs;

use App\Models\PostNummer;
use App\Traits\SendsFilamentNotifications;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdatePostNummersTable implements ShouldQueue
{
    use Queueable;
    use SendsFilamentNotifications;

    public int $timeout = 300; // 5 minutes timeout

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $event,
        public array $data,
        public string $timestamp
    ) {
        $this->onQueue('postnummer-updates');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("[PostNummers Update] Processing event: {$this->event}", [
            'data' => $this->data,
            'timestamp' => $this->timestamp,
        ]);

        try {
            // Update post-nummers table based on the event type
            match ($this->event) {
                'mount' => $this->handleMountEvent(),
                'updating' => $this->handleUpdatingEvent(),
                'updated' => $this->handleUpdatedEvent(),
                default => Log::warning("[PostNummers Update] Unknown event type: {$this->event}")
            };

            Log::info("[PostNummers Update] Successfully processed event: {$this->event}");

            // Send success notification
            $this->sendJobCompletedNotification(
                jobName: 'Post Nummers Update',
                details: [
                    'Event' => $this->event,
                    'Timestamp' => $this->timestamp,
                ]
            );

        } catch (Exception $e) {
            Log::error("[PostNummers Update] Failed to process event {$this->event}: " . $e->getMessage(), [
                'data' => $this->data,
                'timestamp' => $this->timestamp,
            ]);

            throw $e;
        }
    }

    /**
     * Handle component mount event
     */
    protected function handleMountEvent(): void
    {
        // Update last_livewire_update timestamp for all active records
        PostNummer::where('is_active', true)
            ->update([
                'last_livewire_update' => now(),
                'updated_at' => now(),
            ]);

        Log::info('[PostNummers Update] Updated last_livewire_update for active records on mount');
    }

    /**
     * Handle property updating event
     */
    protected function handleUpdatingEvent(): void
    {
        $property = $this->data['property'] ?? 'unknown';
        $value = $this->data['value'] ?? null;

        // Log the property change
        Log::info("[PostNummers Update] Property {$property} is being updated", [
            'old_value' => $this->getCurrentValue($property),
            'new_value' => $value,
        ]);

        // You can add specific logic here based on which property is being updated
        // For example, if updating status, you might want to trigger additional actions
    }

    /**
     * Handle property updated event
     */
    protected function handleUpdatedEvent(): void
    {
        $property = $this->data['property'] ?? 'unknown';
        $value = $this->data['value'] ?? null;

        // Update the corresponding field in post_nummers table
        $this->updatePostNummerField($property, $value);

        // Update last_livewire_update timestamp
        PostNummer::where('is_active', true)
            ->update([
                'last_livewire_update' => now(),
                'updated_at' => now(),
            ]);

        Log::info("[PostNummers Update] Property {$property} was updated to: " . json_encode($value));
    }

    /**
     * Update specific field in post_nummers table
     */
    protected function updatePostNummerField(string $property, $value): void
    {
        // Map Livewire properties to post_nummers table fields
        $fieldMapping = [
            'status' => 'status',
            'is_active' => 'is_active',
            'progress' => 'progress',
            'total_count' => 'total_count',
            'count' => 'count',
            'phone' => 'phone',
            'house' => 'house',
            'bolag' => 'bolag',
            'foretag' => 'foretag',
            'personer' => 'personer',
            'platser' => 'platser',
        ];

        if (isset($fieldMapping[$property])) {
            $dbField = $fieldMapping[$property];

            // Update all active records or find specific record based on context
            // For now, we'll update active records
            PostNummer::where('is_active', true)
                ->update([
                    $dbField => $value,
                    'updated_at' => now(),
                ]);

            Log::info("[PostNummers Update] Updated field {$dbField} to {$value} for active records");
        }
    }

    /**
     * Get current value of a property (for logging purposes)
     */
    protected function getCurrentValue(string $property): mixed
    {
        // This is a simplified version - in a real implementation,
        // you might want to get the current value from the database or cache
        return null;
    }

    /**
     * Handle job failure
     */
    public function failed(Throwable $exception): void
    {
        Log::error('[PostNummers Update] Job failed: ' . $exception->getMessage(), [
            'event' => $this->event,
            'data' => $this->data,
            'timestamp' => $this->timestamp,
        ]);

        // Send failure notification
        $this->sendJobFailedNotification(
            jobName: 'Post Nummers Update',
            error: $exception->getMessage(),
            details: [
                'Event' => $this->event,
                'Timestamp' => $this->timestamp,
            ]
        );
    }
}
