<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatsitAdresserSverige extends Model
{
    protected $table = 'ratsit_adresser_sverige';

    protected $fillable = [
        'post_ort',
        'post_nummer',
        'gatuadress_namn',
        'gatuadress_count',
        'gatuadress_nummer_link',
    ];
}
