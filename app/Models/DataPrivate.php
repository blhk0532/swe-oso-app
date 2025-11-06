<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPrivate extends Model
{
    /** @use HasFactory<\Database\Factories\DataPrivateFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'data_private';

    /**
     * @var array<string, string>
     */
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

    /**
     * @var array<int, string>
     */
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

    /**
     * Scope a query to only include active records.
     *
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by postal code.
     *
     * @return Builder<static>
     */
    public function scopeByPostnummer(Builder $query, string $postnummer): Builder
    {
        return $query->where('bo_postnummer', $postnummer);
    }

    /**
     * Scope a query to filter by city.
     *
     * @return Builder<static>
     */
    public function scopeByPostort(Builder $query, string $postort): Builder
    {
        return $query->where('bo_postort', $postort);
    }

    /**
     * Scope a query to filter by municipality.
     *
     * @return Builder<static>
     */
    public function scopeByKommun(Builder $query, string $kommun): Builder
    {
        return $query->where('bo_kommun', $kommun);
    }

    /**
     * Scope a query to filter by state.
     *
     * @return Builder<static>
     */
    public function scopeByLan(Builder $query, string $lan): Builder
    {
        return $query->where('bo_lan', $lan);
    }

    /**
     * Scope a query to filter by personnummer.
     *
     * @return Builder<static>
     */
    public function scopeByPersonnummer(Builder $query, string $personnummer): Builder
    {
        return $query->where('ps_personnummer', $personnummer);
    }

    /**
     * Scope a query to search by person name.
     *
     * @return Builder<static>
     */
    public function scopeSearchByName(Builder $query, string $name): Builder
    {
        return $query->where('ps_personnamn', 'ilike', "%{$name}%")
            ->orWhere('ps_fornamn', 'ilike', "%{$name}%")
            ->orWhere('ps_efternamn', 'ilike', "%{$name}%");
    }
}
