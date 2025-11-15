<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerinfoQueue extends Model
{
    protected $table = 'merinfo_queue';

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
        'foretag_total',
        'personer_total',
        'personer_house',
        'foretag_phone',
        'personer_phone',
        'foretag_saved',
        'personer_saved',
        'foretag_queued',
        'personer_queued',
        'foretag_scraped',
        'personer_scraped',
        'is_active',
    ];

    protected $casts = [
        'foretag_total' => 'integer',
        'personer_total' => 'integer',
        'personer_house' => 'integer',
        'foretag_phone' => 'integer',
        'personer_phone' => 'integer',
        'foretag_saved' => 'integer',
        'personer_saved' => 'integer',
        'foretag_queued' => 'boolean',
        'personer_queued' => 'boolean',
        'foretag_scraped' => 'boolean',
        'personer_scraped' => 'boolean',
        'is_active' => 'boolean',
    ];
}
