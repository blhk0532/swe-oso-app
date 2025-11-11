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
            'last_processed_page' => 'integer',
            'processed_count' => 'integer',
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
        'status',
        'progress',
        'is_pending',
        'is_complete',
        'is_active',
        'last_processed_page',
        'processed_count',
    ];
}
