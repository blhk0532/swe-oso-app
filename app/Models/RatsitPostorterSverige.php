<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatsitPostorterSverige extends Model
{
    protected $table = 'ratsit_postorter_sverige';

    protected $fillable = [
        'post_ort',
        'post_nummer',
        'post_nummer_count',
        'post_nummer_link',
    ];
}
