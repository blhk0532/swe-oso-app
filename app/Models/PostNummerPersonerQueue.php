<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostNummerPersonerQueue extends Model
{
    protected $table = 'post_nummer_personer_queue';

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
        'merinfo_personer_saved',
        'merinfo_personer_total',
        'merinfo_status',
        'ratsit_personer_saved',
        'ratsit_personer_total',
        'ratsit_status',
        'hitta_personer_saved',
        'hitta_personer_total',
        'hitta_status',
        'post_nummer_personer_saved',
        'post_nummer_personer_total',
        'post_nummer_status',
        'is_active',
    ];

    protected $casts = [
        'merinfo_personer_saved' => 'integer',
        'merinfo_personer_total' => 'integer',
        'ratsit_personer_saved' => 'integer',
        'ratsit_personer_total' => 'integer',
        'hitta_personer_saved' => 'integer',
        'hitta_personer_total' => 'integer',
        'post_nummer_personer_saved' => 'integer',
        'post_nummer_personer_total' => 'integer',
        'is_active' => 'boolean',
    ];
}
