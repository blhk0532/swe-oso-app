<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HittaSe extends Model
{
    protected $table = 'hitta_se';

    protected $fillable = [
        'personnamn',
        'alder',
        'kon',
        'gatuadress',
        'postnummer',
        'postort',
        'telefon',
        'karta',
        'link',
        'is_active',
        'is_telefon',
        'is_ratsit',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_telefon' => 'boolean',
        'is_ratsit' => 'boolean',
        'telefon' => 'array',
    ];

    protected $attributes = [
        'telefon' => '[]',
    ];
}
