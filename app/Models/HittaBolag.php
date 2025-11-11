<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HittaBolag extends Model
{
    use HasFactory;

    protected $table = 'hitta_bolag';

    protected $fillable = [
        'personnamn', // original name captured by scraper, may map to juridiskt_namn logically
        'juridiskt_namn',
        'registreringsdatum',
        'org_nr',
        'bolagsform',
        'sni_branch',
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
            'sni_branch' => 'array',
            'is_active' => 'boolean',
            'is_telefon' => 'boolean',
            'is_ratsit' => 'boolean',
            'registreringsdatum' => 'date',
        ];
    }
}
