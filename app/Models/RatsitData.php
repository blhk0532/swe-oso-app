<?php

namespace App\Models;

use App\Casts\SwedishDateCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatsitData extends Model
{
    /** @use HasFactory<\Database\Factories\RatsitDataFactory> */
    use HasFactory;

    protected $table = 'ratsit_data';

    protected $casts = [
        'fodelsedag' => SwedishDateCast::class,
        'telefon' => 'array',
        'epost_adress' => 'array',
        'bolagsengagemang' => 'array',
        'personer' => 'array',
        'foretag' => 'array',
        'grannar' => 'array',
        'fordon' => 'array',
        'hundar' => 'array',
        'is_active' => 'boolean',
        'longitude' => 'decimal:7',
        'latitud' => 'decimal:7',
    ];

    protected $fillable = [
        'gatuadress',
        'postnummer',
        'postort',
        'forsamling',
        'kommun',
        'lan',
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
        'bolagsengagemang',
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
        'longitude',
        'latitud',
        'is_active',
    ];

    /** @return Builder<static> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
