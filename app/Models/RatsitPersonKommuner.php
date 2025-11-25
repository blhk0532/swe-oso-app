<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatsitPersonKommuner extends Model
{
    protected $table = 'ratsit_person_kommuner';

    protected $fillable = [
        'kommun',
        'person_count',
        'ratsit_link',
        'person_postort_saved',
    ];
}
