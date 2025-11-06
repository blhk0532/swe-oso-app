<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatsitData extends Model
{
    /** @use HasFactory<\Database\Factories\RatsitDataFactory> */
    use HasFactory;

    protected $table = 'ratsit_data';

    protected $casts = [
        'ps_fodelsedag' => 'date',
        'ps_telefon' => 'array',
        'ps_epost_adress' => 'array',
        'ps_bolagsengagemang' => 'array',
        'bo_personer' => 'array',
        'bo_foretag' => 'array',
        'bo_grannar' => 'array',
        'bo_fordon' => 'array',
        'bo_hundar' => 'array',
        'is_active' => 'boolean',
        'bo_longitude' => 'decimal:7',
        'bo_latitud' => 'decimal:7',
    ];

    protected $fillable = [
        'bo_gatuadress',
        'bo_postnummer',
        'bo_postort',
        'bo_forsamling',
        'bo_kommun',
        'bo_lan',
        'ps_fodelsedag',
        'ps_personnummer',
        'ps_alder',
        'ps_kon',
        'ps_civilstand',
        'ps_fornamn',
        'ps_efternamn',
        'ps_personnamn',
        'ps_telefon',
        'ps_epost_adress',
        'ps_bolagsengagemang',
        'bo_agandeform',
        'bo_bostadstyp',
        'bo_boarea',
        'bo_byggar',
        'bo_fastighet',
        'bo_personer',
        'bo_foretag',
        'bo_grannar',
        'bo_fordon',
        'bo_hundar',
        'bo_longitude',
        'bo_latitud',
        'is_active',
    ];

    /** @return Builder<static> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
