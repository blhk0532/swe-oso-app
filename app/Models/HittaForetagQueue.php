<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HittaForetagQueue extends Model
{
    protected $table = 'hitta_foretag_queue';

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
        'foretag_phone',
        'foretag_house',
        'foretag_saved',
        'foretag_total',
        'foretag_page',
        'foretag_pages',
        'foretag_status',
        'foretag_queued',
        'foretag_scraped',
        'is_active',
    ];

    protected $casts = [
        'foretag_phone' => 'integer',
        'foretag_house' => 'integer',
        'foretag_saved' => 'integer',
        'foretag_total' => 'integer',
        'foretag_page' => 'integer',
        'foretag_pages' => 'integer',
        'foretag_queued' => 'boolean',
        'foretag_scraped' => 'boolean',
        'is_active' => 'boolean',
    ];
}
