<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostNummerApi extends Model
{
    protected $connection = 'postnummer';

    protected $table = 'post_nummer';

    protected $fillable = [
        'post_nummer',
        'post_ort',
        'post_lan',
    ];

    public $timestamps = false; // The table doesn't have created_at/updated_at columns
}
