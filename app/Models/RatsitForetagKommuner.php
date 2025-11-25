<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatsitForetagKommuner extends Model
{
    protected $table = 'ratsit_foretag_kommuner';

    protected $fillable = [
        'kommun',
        'foretag_count',
        'ratsit_link',
        'foretag_postort_saved',
    ];
}
