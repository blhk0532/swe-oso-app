<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HittaData extends Model
{
    use HasFactory;

    protected $table = 'hitta_data';

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
        'bostadstyp',
        'bostadspris',
        'is_active',
        'is_telefon',
        'is_ratsit',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_telefon' => 'boolean',
            'is_ratsit' => 'boolean',
        ];
    }
}
