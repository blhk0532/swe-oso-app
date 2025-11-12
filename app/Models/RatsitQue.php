<?php

namespace App\Models;

use App\Casts\SwedishDateCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatsitQue extends Model
{
    /** @use HasFactory<\Database\Factories\RatsitQueFactory> */
    use HasFactory;

    protected $table = 'ratsit_que';

    protected $casts = [
        'fodelsedag' => SwedishDateCast::class,
        'telefon' => 'array',
        'telfonnummer' => 'array',
        'epost_adress' => 'array',
        'bolagsengagemang' => 'array',
        'personer' => 'array',
        'foretag' => 'array',
        'grannar' => 'array',
        'fordon' => 'array',
        'hundar' => 'array',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'gatuadress',
        'postnummer',
        'postort',
        'forsamling',
        'kommun',
        'lan',
        'adressandring',
        'telfonnummer',
        'stjarntacken',
        'fodelsedag',
        'personnummer',
        'alder',
        'kon',
        'civilstand',
        'fornamn',
        'efternamn',
        'personnamn',
        'telefon',
        'epost_adress',
        'agandeform',
        'bostadstyp',
        'boarea',
        'byggar',
        'fastighet',
        'personer',
        'foretag',
        'grannar',
        'fordon',
        'hundar',
        'bolagsengagemang',
        'longitude',
        'latitud',
        'google_maps',
        'google_streetview',
        'ratsit_se',
        'is_active',
    ];

    /** @return Builder<static> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
