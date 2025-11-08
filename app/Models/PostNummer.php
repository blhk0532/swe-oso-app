<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostNummer extends Model
{
    /** @use HasFactory<\Database\Factories\PostNummerFactory> */
    use HasFactory;

    protected $table = 'post_nummer';

    protected function casts(): array
    {
        return [
            'is_pending' => 'boolean',
            'is_complete' => 'boolean',
            'is_active' => 'boolean',
            'total_count' => 'integer',
        ];
    }

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'total_count',
        'status',
        'is_pending',
        'is_complete',
        'is_active',
    ];
}
