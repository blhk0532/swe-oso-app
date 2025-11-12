<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostNummerQue extends Model
{
    /** @use HasFactory<\Database\Factories\PostNummerQueFactory> */
    use HasFactory;

    protected $table = 'post_nummer_que';

    /**
     * Default attribute values for new records.
     */
    protected $attributes = [
        'phone' => 0,
        'house' => 0,
        'bolag' => 0,
        'foretag' => 0,
        'personer' => 0,
        'platser' => 0,
    ];

    /**
     * Attribute casting for the model.
     *
     * NOTE: use the $casts property (not a casts() method) so Eloquent applies
     * the correct type casting for boolean/integer fields.
     *
     * @var array<string,string>
     */
    protected $casts = [
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
        'platser' => 'integer',
        'last_processed_page' => 'integer',
        'processed_count' => 'integer',
    ];

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
    ];
}
