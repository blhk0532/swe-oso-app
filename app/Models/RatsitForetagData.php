<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatsitForetagData extends Model
{
    use HasFactory;

    protected $table = 'ratsit_foretag_data';

    protected $fillable = [
        'gatuadress',
        'postnummer',
        'postort',
        'telfonnummer',
        'telefon',
        'epost_adress',
        'longitude',
        'latitud',
        'google_maps',
        'google_streetview',
        'ratsit_se',
        'is_active',
    ];

    protected $casts = [
        'telfonnummer' => 'array',
        'telefon' => 'array',
        'epost_adress' => 'array',
        'is_active' => 'boolean',
    ];
}
