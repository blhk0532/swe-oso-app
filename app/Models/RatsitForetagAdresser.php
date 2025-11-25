<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatsitForetagAdresser extends Model
{
    protected $table = 'ratsit_foretag_adresser';

    protected $fillable = [
        'post_ort',
        'post_nummer',
        'gatuadress_namn',
        'foretag_count',
        'ratsit_link',
    ];
}
