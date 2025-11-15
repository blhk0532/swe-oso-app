<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HittaPersonerQueue extends Model
{
    protected $table = 'hitta_personer_queue';

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
        'personer_phone',
        'personer_house',
        'personer_saved',
        'personer_total',
        'personer_page',
        'personer_pages',
        'personer_status',
        'personer_queued',
        'personer_scraped',
        'is_active',
    ];

    protected $casts = [
        'personer_phone' => 'integer',
        'personer_house' => 'integer',
        'personer_saved' => 'integer',
        'personer_total' => 'integer',
        'personer_page' => 'integer',
        'personer_pages' => 'integer',
        'personer_queued' => 'boolean',
        'personer_scraped' => 'boolean',
        'is_active' => 'boolean',
    ];
}
