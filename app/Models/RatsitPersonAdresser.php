<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatsitPersonAdresser extends Model
{
    protected $table = 'ratsit_person_adresser';

    protected $fillable = [
        'post_ort',
        'post_nummer',
        'gatuadress_namn',
        'person_count',
        'ratsit_link',
    ];
}
