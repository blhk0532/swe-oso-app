<?php

namespace App\Models;

use App\Casts\SwedishDateCast;
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
    protected $table = 'private_data';

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'fodelsedag' => SwedishDateCast::class,
        'telefon' => 'array',
        'telfonnummer' => 'array',
        'bolagsengagemang' => 'array',
        'personer' => 'array',
        'foretag' => 'array',
        'grannar' => 'array',
        'fordon' => 'array',
        'hundar' => 'array',
        'is_active' => 'boolean',
        'is_update' => 'boolean',
        'longitude' => 'string',
        'latitud' => 'string',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'gatuadress',
        'postnummer',
        'postort',
        'forsamling',
        'kommun',
        'lan',
        'adressandring',
        'fodelsedag',
        'personnummer',
        'alder',
        'kon',
        'civilstand',
        'fornamn',
        'efternamn',
        'personnamn',
        'telefon',
        'telfonnummer',
        'bolagsengagemang',
        'agandeform',
        'bostadstyp',
        'boarea',
        'byggar',
        'personer',
        'foretag',
        'grannar',
        'fordon',
        'hundar',
        'longitude',
        'latitud',
        'google_maps',
        'google_streetview',
        'ratsit_link',
        'hitta_link',
        'hitta_karta',
        'bostad_typ',
        'bostad_pris',
        'is_active',
        'is_update',
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
        return $query->where('postnummer', $postnummer);
    }

    /**
     * Scope a query to filter by city.
     *
     * @return Builder<static>
     */
    public function scopeByPostort(Builder $query, string $postort): Builder
    {
        return $query->where('postort', $postort);
    }

    /**
     * Scope a query to filter by municipality.
     *
     * @return Builder<static>
     */
    public function scopeByKommun(Builder $query, string $kommun): Builder
    {
        return $query->where('kommun', $kommun);
    }

    /**
     * Scope a query to filter by state.
     *
     * @return Builder<static>
     */
    public function scopeByLan(Builder $query, string $lan): Builder
    {
        return $query->where('lan', $lan);
    }

    /**
     * Scope a query to filter by personnummer.
     *
     * @return Builder<static>
     */
    public function scopeByPersonnummer(Builder $query, string $personnummer): Builder
    {
        return $query->where('personnummer', $personnummer);
    }

    /**
     * Scope a query to search by person name.
     *
     * @return Builder<static>
     */
    public function scopeSearchByName(Builder $query, string $name): Builder
    {
        return $query->where('personnamn', 'ilike', "%{$name}%")
            ->orWhere('fornamn', 'ilike', "%{$name}%")
            ->orWhere('efternamn', 'ilike', "%{$name}%");
    }
}
