<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatsitPersonerData extends Model
{
    use HasFactory;

    protected $table = 'ratsit_personer_data';

    protected $fillable = [
        'personnamn',
        'personnummer',
        'fornamn',
        'efternamn',
        'gatuadress',
        'postnummer',
        'postort',
        'telefon',
        'karta',
        'link',
        'is_active',
        'is_telefon',
        'is_ratsit',
        'is_hus',
        'ratsit_personer_total',
        'ratsit_foretag_total',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_telefon' => 'boolean',
        'is_ratsit' => 'boolean',
        'is_hus' => 'boolean',
        'telefon' => 'array',
        'ratsit_personer_total' => 'integer',
        'ratsit_foretag_total' => 'integer',
    ];
}
