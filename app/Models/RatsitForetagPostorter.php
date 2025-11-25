<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatsitForetagPostorter extends Model
{
    protected $table = 'ratsit_foretag_postorter';

    protected $fillable = [
        'post_ort',
        'post_nummer',
        'foretag_count',
        'ratsit_link',
    ];
}
