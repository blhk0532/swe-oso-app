<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatsitPersonPostorter extends Model
{
    protected $table = 'ratsit_person_postorter';

    protected $fillable = [
        'post_ort',
        'post_nummer',
        'person_count',
        'ratsit_link',
    ];
}
