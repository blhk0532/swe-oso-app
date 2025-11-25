<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostNummer extends Model
{
    /** @use HasFactory<\Database\Factories\PostNummerFactory> */
    use HasFactory;

    protected $table = 'post_nummer';

    /**
     * Default attribute values for new records.
     */
    protected $attributes = [
        'phone' => 0,
        'house' => 0,
        'bolag' => 0,
        'foretag' => 0,
        'personer' => 0,
        'personer_house' => 0,
        'platser' => 0,
    ];

    protected function casts(): array
    {
        return [
            'is_pending' => 'boolean',
            'is_complete' => 'boolean',
            'is_active' => 'boolean',
            'total_count' => 'integer',
            'progress' => 'integer',
            'count' => 'integer',
            'phone' => 'integer',
            'house' => 'integer',
            'bolag' => 'integer',
            'foretag' => 'integer',
            'personer' => 'integer',
            'personer_house' => 'integer',
            'platser' => 'integer',
            'last_processed_page' => 'integer',
            'processed_count' => 'integer',
            'merinfo_personer' => 'integer',
            'merinfo_foretag' => 'integer',
            'merinfo_personer_total' => 'integer',
            'merinfo_foretag_total' => 'integer',
            'ratsit_personer_total' => 'integer',
            'ratsit_foretag_total' => 'integer',
            'last_livewire_update' => 'datetime',
            'status' => 'string',
        ];
    }

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
        'total_count',
        'count',
        'phone',
        'house',
        'bolag',
        'foretag',
        'personer',
        'platser',
        'status',
        'progress',
        'is_pending',
        'is_complete',
        'is_active',
        'last_processed_page',
        'processed_count',
        'merinfo_personer',
        'merinfo_foretag',
        'ratsit_personer_total',
        'ratsit_foretag_total',
        'merinfo_personer_total',
        'merinfo_foretag_total',
        'last_livewire_update',
    ];

    /**
     * Increment counter safely with atomic operations
     * This prevents race conditions when multiple processes update counters
     */
    public function incrementCounter(string $field, int $amount = 1): bool
    {
        if (! in_array($field, $this->fillable)) {
            return false;
        }

        // Use database atomic increment to prevent race conditions
        $this->increment($field, $amount);

        return true;
    }

    /**
     * Set counter value safely
     */
    public function setCounter(string $field, int $value): bool
    {
        if (! in_array($field, $this->fillable)) {
            return false;
        }

        $this->update([$field => $value]);

        return true;
    }

    /**
     * Get current counter value
     */
    public function getCounter(string $field): ?int
    {
        return $this->getAttribute($field);
    }

    /**
     * Reset counters for a fresh start
     */
    public function resetCounters(): bool
    {
        $counterFields = [
            'count', 'phone', 'house', 'bolag', 'foretag', 'personer', 'platser',
            'processed_count', 'merinfo_personer', 'merinfo_foretag',
        ];

        $resetData = array_fill_keys($counterFields, 0);
        $resetData['progress'] = 0;
        $resetData['last_processed_page'] = 0;
        $resetData['is_pending'] = false;
        $resetData['is_complete'] = false;

        $this->update($resetData);

        return true;
    }

    /**
     * Mark as completed and calculate final progress
     */
    public function markCompleted(): bool
    {
        $this->update([
            'is_complete' => true,
            'is_pending' => false,
            'progress' => 100,
            'status' => 'completed',
        ]);

        return true;
    }

    /**
     * Check if processing can resume from last state
     */
    public function canResume(): bool
    {
        return $this->is_pending && ! $this->is_complete && $this->last_processed_page > 0;
    }

    /**
     * Get resume information for scripts
     */
    public function getResumeInfo(): array
    {
        return [
            'post_nummer' => $this->post_nummer,
            'last_processed_page' => $this->last_processed_page,
            'processed_count' => $this->processed_count,
            'progress' => $this->progress,
            'can_resume' => $this->canResume(),
        ];
    }
}
