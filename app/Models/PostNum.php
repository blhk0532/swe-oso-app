<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostNum extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'post_nummer',
        'post_ort',
        'post_lan',
        'hitta_personer_total',
        'hitta_foretag_total',
        'ratsit_personer_total',
        'ratsit_foretag_total',
        'merinfo_personer_total',
        'merinfo_foretag_total',
        'is_active',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'post_nummer' => 'string',
        'post_ort' => 'string',
        'post_lan' => 'string',
        'hitta_personer_total' => 'integer',
        'hitta_foretag_total' => 'integer',
        'ratsit_personer_total' => 'integer',
        'ratsit_foretag_total' => 'integer',
        'merinfo_personer_total' => 'integer',
        'merinfo_foretag_total' => 'integer',
        'is_active' => 'boolean',
        'status' => 'string',
    ];

    public function getTable(): string
    {
        return 'post_nums';
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
