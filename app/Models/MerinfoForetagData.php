<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerinfoForetagData extends Model
{
    use HasFactory;

    protected $table = 'merinfo_foretag_data';

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
        'is_hus',
        'merinfo_personer_total',
        'merinfo_foretag_total',
    ];

    protected $casts = [
        'telefon' => 'array',
        'is_active' => 'boolean',
        'is_telefon' => 'boolean',
        'is_ratsit' => 'boolean',
        'is_hus' => 'boolean',
        'merinfo_personer_total' => 'integer',
        'merinfo_foretag_total' => 'integer',
    ];
}
