<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HittaForetagData extends Model
{
    use HasFactory;

    protected $table = 'hitta_foretag_data';

    protected $fillable = [
        'foretagsnamn',
        'orgnummer',
        'gatuadress',
        'postnummer',
        'postort',
        'telefon',
        'karta',
        'link',
        'bostadstyp',
        'bostadspris',
        'is_active',
        'is_telefon',
        'is_ratsit',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_telefon' => 'boolean',
        'is_ratsit' => 'boolean',
    ];
}
