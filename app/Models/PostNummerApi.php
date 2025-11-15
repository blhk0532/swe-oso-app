<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostNummerApi extends Model
{
    protected $connection = 'sqlite';

    protected $table = 'post_nummer';

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
        'personer_house',
        'merinfo_personer',
        'merinfo_foretag',
        'platser',
        'status',
        'progress',
        'is_pending',
        'is_complete',
        'is_active',
        'last_processed_page',
        'processed_count',
    ];

    protected $casts = [
        'is_pending' => 'boolean',
        'is_complete' => 'boolean',
        'is_active' => 'boolean',
        'total_count' => 'integer',
        'count' => 'integer',
        'phone' => 'integer',
        'house' => 'integer',
        'bolag' => 'integer',
        'foretag' => 'integer',
        'personer' => 'integer',
        'personer_house' => 'integer',
        'merinfo_personer' => 'integer',
        'merinfo_foretag' => 'integer',
        'platser' => 'integer',
        'progress' => 'integer',
        'last_processed_page' => 'integer',
        'processed_count' => 'integer',
    ];

    /**
     * Increment a counter field atomically
     */
    public function incrementCounter(string $field, int $amount = 1): bool
    {
        if (! in_array($field, $this->fillable)) {
            return false;
        }

        return $this->increment($field, $amount);
    }

    /**
     * Reset all counters to zero
     */
    public function resetCounters(): bool
    {
        $counterFields = [
            'count', 'phone', 'house', 'bolag', 'foretag', 'personer',
            'personer_house', 'merinfo_personer', 'merinfo_foretag', 'platser', 'processed_count',
        ];

        $updates = array_fill_keys($counterFields, 0);
        $updates['progress'] = 0;
        $updates['status'] = 'pending';
        $updates['is_pending'] = true;
        $updates['is_complete'] = false;
        $updates['last_processed_page'] = 0;

        return $this->update($updates);
    }

    /**
     * Check if the record can resume processing
     */
    public function canResume(): bool
    {
        return $this->is_pending && ! $this->is_complete && $this->status !== 'error';
    }
}
