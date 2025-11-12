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
        'merinfo_personer' => 'integer',
        'merinfo_foretag' => 'integer',
        'platser' => 'integer',
        'progress' => 'integer',
        'last_processed_page' => 'integer',
        'processed_count' => 'integer',
    ];
}
