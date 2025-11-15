<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostNummerQueue extends Model
{
    use HasFactory;

    protected $table = 'post_nummer_queue';

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
        'merinfo_personer_saved',
        'merinfo_foretag_saved',
        'merinfo_personer_total',
        'merinfo_foretag_total',
        'merinfo_status',
        'merinfo_checked',
        'merinfo_queued',
        'merinfo_scraped',
        'merinfo_complete',
        'ratsit_personer_saved',
        'ratsit_foretag_saved',
        'ratsit_personer_total',
        'ratsit_foretag_total',
        'ratsit_status',
        'ratsit_checked',
        'ratsit_queued',
        'ratsit_scraped',
        'ratsit_complete',
        'hitta_personer_saved',
        'hitta_foretag_saved',
        'hitta_personer_total',
        'hitta_foretag_total',
        'hitta_status',
        'hitta_checked',
        'hitta_queued',
        'hitta_scraped',
        'hitta_complete',
        'post_nummer_personer_saved',
        'post_nummer_foretag_saved',
        'post_nummer_personer_total',
        'post_nummer_foretag_total',
        'post_nummer_status',
        'post_nummer_checked',
        'post_nummer_queued',
        'post_nummer_scraped',
        'post_nummer_complete',
        'is_active',
    ];

    protected $casts = [
        'merinfo_personer_saved' => 'integer',
        'merinfo_foretag_saved' => 'integer',
        'merinfo_personer_total' => 'integer',
        'merinfo_foretag_total' => 'integer',
        'merinfo_checked' => 'boolean',
        'merinfo_queued' => 'boolean',
        'merinfo_scraped' => 'boolean',
        'merinfo_complete' => 'boolean',
        'ratsit_personer_saved' => 'integer',
        'ratsit_foretag_saved' => 'integer',
        'ratsit_personer_total' => 'integer',
        'ratsit_foretag_total' => 'integer',
        'ratsit_checked' => 'boolean',
        'ratsit_queued' => 'boolean',
        'ratsit_scraped' => 'boolean',
        'ratsit_complete' => 'boolean',
        'hitta_personer_saved' => 'integer',
        'hitta_foretag_saved' => 'integer',
        'hitta_personer_total' => 'integer',
        'hitta_foretag_total' => 'integer',
        'hitta_checked' => 'boolean',
        'hitta_queued' => 'boolean',
        'hitta_scraped' => 'boolean',
        'hitta_complete' => 'boolean',
        'post_nummer_personer_saved' => 'integer',
        'post_nummer_foretag_saved' => 'integer',
        'post_nummer_personer_total' => 'integer',
        'post_nummer_foretag_total' => 'integer',
        'post_nummer_checked' => 'boolean',
        'post_nummer_queued' => 'boolean',
        'post_nummer_scraped' => 'boolean',
        'post_nummer_complete' => 'boolean',
        'is_active' => 'boolean',
    ];
}
