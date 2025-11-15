<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HittaQueue extends Model
{
    protected $table = 'hitta_queue';

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
        'foretag_total',
        'personer_total',
        'foretag_phone',
        'personer_phone',
        'personer_house',
        'foretag_saved',
        'personer_saved',
        'personer_pages',
        'personer_page',
        'personer_status',
        'foretag_status',
        'foretag_queued',
        'personer_queued',
        'foretag_scraped',
        'personer_scraped',
        'is_active',
    ];

    protected $casts = [
        'foretag_total' => 'integer',
        'personer_total' => 'integer',
        'foretag_phone' => 'integer',
        'personer_phone' => 'integer',
        'personer_house' => 'integer',
        'foretag_saved' => 'integer',
        'personer_saved' => 'integer',
        'personer_pages' => 'integer',
        'personer_page' => 'integer',
        'foretag_queued' => 'boolean',
        'personer_queued' => 'boolean',
        'foretag_scraped' => 'boolean',
        'personer_scraped' => 'boolean',
        'is_active' => 'boolean',
    ];
}
