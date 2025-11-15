<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostNummerForetagQueue extends Model
{
    protected $table = 'post_nummer_foretag_queue';

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
        'merinfo_foretag_saved',
        'merinfo_foretag_total',
        'merinfo_status',
        'ratsit_foretag_saved',
        'ratsit_foretag_total',
        'ratsit_status',
        'hitta_foretag_saved',
        'hitta_foretag_total',
        'hitta_status',
        'post_nummer_foretag_saved',
        'post_nummer_foretag_total',
        'post_nummer_status',
        'is_active',
    ];

    protected $casts = [
        'merinfo_foretag_saved' => 'integer',
        'merinfo_foretag_total' => 'integer',
        'ratsit_foretag_saved' => 'integer',
        'ratsit_foretag_total' => 'integer',
        'hitta_foretag_saved' => 'integer',
        'hitta_foretag_total' => 'integer',
        'post_nummer_foretag_saved' => 'integer',
        'post_nummer_foretag_total' => 'integer',
        'is_active' => 'boolean',
    ];
}
