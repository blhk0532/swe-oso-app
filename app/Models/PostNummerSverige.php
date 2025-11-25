<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostNummerSverige extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'post_nummer',
        'post_ort',
        'post_lan',
        'hitta_personer_queued',
        'hitta_foretag_queued',
        'hitta_personer_checked',
        'hitta_foretag_checked',
        'hitta_personer_saved',
        'hitta_foretag_saved',
        'hitta_personer_phone',
        'hitta_foretag_phone',
        'hitta_personer_house',
        'hitta_foretag_house',
        'hitta_personer_count',
        'hitta_foretag_count',
        'hitta_personer_total',
        'hitta_foretag_total',
        'hitta_personer_status',
        'hitta_foretag_status',
        'hitta_personer_is_active',
        'hitta_foretag_is_active',
        'hitta_personer_updated_at',
        'hitta_foretag_updated_at',
        'merinfo_personer_queued',
        'merinfo_foretag_queued',
        'merinfo_personer_checked',
        'merinfo_foretag_checked',
        'merinfo_personer_saved',
        'merinfo_foretag_saved',
        'merinfo_personer_phone',
        'merinfo_foretag_phone',
        'merinfo_personer_house',
        'merinfo_foretag_house',
        'merinfo_personer_count',
        'merinfo_foretag_count',
        'merinfo_personer_total',
        'merinfo_foretag_total',
        'merinfo_personer_status',
        'merinfo_foretag_status',
        'merinfo_personer_is_active',
        'merinfo_foretag_is_active',
        'merinfo_personer_updated_at',
        'merinfo_foretag_updated_at',
        'ratsit_personer_queued',
        'ratsit_foretag_queued',
        'ratsit_personer_checked',
        'ratsit_foretag_checked',
        'ratsit_personer_saved',
        'ratsit_foretag_saved',
        'ratsit_personer_phone',
        'ratsit_foretag_phone',
        'ratsit_personer_house',
        'ratsit_foretag_house',
        'ratsit_personer_count',
        'ratsit_foretag_count',
        'ratsit_personer_total',
        'ratsit_foretag_total',
        'ratsit_personer_status',
        'ratsit_foretag_status',
        'ratsit_personer_is_active',
        'ratsit_foretag_is_active',
        'ratsit_personer_updated_at',
        'ratsit_foretag_updated_at',
        'personer_pending',
        'foretag_pending',
        'personer_complete',
        'foretag_complete',
        'personer_status',
        'foretag_status',
        'is_active',
    ];

    protected $casts = [
        'id' => 'string',
        'post_nummer' => 'string',
        'post_ort' => 'string',
        'post_lan' => 'string',
        'hitta_personer_queued' => 'boolean',
        'hitta_foretag_queued' => 'boolean',
        'hitta_personer_checked' => 'boolean',
        'hitta_foretag_checked' => 'boolean',
        'hitta_personer_saved' => 'boolean',
        'hitta_foretag_saved' => 'boolean',
        'hitta_personer_phone' => 'boolean',
        'hitta_foretag_phone' => 'boolean',
        'hitta_personer_house' => 'boolean',
        'hitta_foretag_house' => 'boolean',
        'hitta_personer_count' => 'boolean',
        'hitta_foretag_count' => 'boolean',
        'hitta_personer_total' => 'boolean',
        'hitta_foretag_total' => 'boolean',
        'hitta_personer_status' => 'boolean',
        'hitta_foretag_status' => 'boolean',
        'hitta_personer_is_active' => 'boolean',
        'hitta_foretag_is_active' => 'boolean',
        'hitta_personer_updated_at' => 'boolean',
        'hitta_foretag_updated_at' => 'boolean',
        'merinfo_personer_queued' => 'boolean',
        'merinfo_foretag_queued' => 'boolean',
        'merinfo_personer_checked' => 'boolean',
        'merinfo_foretag_checked' => 'boolean',
        'merinfo_personer_saved' => 'boolean',
        'merinfo_foretag_saved' => 'boolean',
        'merinfo_personer_phone' => 'boolean',
        'merinfo_foretag_phone' => 'boolean',
        'merinfo_personer_house' => 'boolean',
        'merinfo_foretag_house' => 'boolean',
        'merinfo_personer_count' => 'boolean',
        'merinfo_foretag_count' => 'boolean',
        'merinfo_personer_total' => 'boolean',
        'merinfo_foretag_total' => 'boolean',
        'merinfo_personer_status' => 'boolean',
        'merinfo_foretag_status' => 'boolean',
        'merinfo_personer_is_active' => 'boolean',
        'merinfo_foretag_is_active' => 'boolean',
        'merinfo_personer_updated_at' => 'boolean',
        'merinfo_foretag_updated_at' => 'boolean',
        'ratsit_personer_queued' => 'boolean',
        'ratsit_foretag_queued' => 'boolean',
        'ratsit_personer_checked' => 'boolean',
        'ratsit_foretag_checked' => 'boolean',
        'ratsit_personer_saved' => 'boolean',
        'ratsit_foretag_saved' => 'boolean',
        'ratsit_personer_phone' => 'boolean',
        'ratsit_foretag_phone' => 'boolean',
        'ratsit_personer_house' => 'boolean',
        'ratsit_foretag_house' => 'boolean',
        'ratsit_personer_count' => 'boolean',
        'ratsit_foretag_count' => 'boolean',
        'ratsit_personer_total' => 'boolean',
        'ratsit_foretag_total' => 'boolean',
        'ratsit_personer_status' => 'boolean',
        'ratsit_foretag_status' => 'boolean',
        'ratsit_personer_is_active' => 'boolean',
        'ratsit_foretag_is_active' => 'boolean',
        'ratsit_personer_updated_at' => 'boolean',
        'ratsit_foretag_updated_at' => 'boolean',
        'personer_pending' => 'boolean',
        'foretag_pending' => 'boolean',
        'personer_complete' => 'boolean',
        'foretag_complete' => 'boolean',
        'personer_status' => 'boolean',
        'foretag_status' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getTable(): string
    {
        return 'post_nummer_sverige';
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getKey(): string
    {
        return $this->getAttribute('id');
    }
}
