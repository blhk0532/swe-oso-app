<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\RatsitData */
class RatsitDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address' => [
                'gatuadress' => $this->gatuadress,
                'postnummer' => $this->postnummer,
                'postort' => $this->postort,
                'forsamling' => $this->forsamling,
                'kommun' => $this->kommun,
                'lan' => $this->lan,
                'longitude' => $this->longitude,
                'latitud' => $this->latitud,
            ],
            'person' => [
                'fodelsedag' => optional($this->fodelsedag)->format('Y-m-d'),
                'personnummer' => $this->personnummer,
                'alder' => $this->alder,
                'kon' => $this->kon,
                'civilstand' => $this->civilstand,
                'fornamn' => $this->fornamn,
                'efternamn' => $this->efternamn,
                'personnamn' => $this->personnamn,
                'telefon' => $this->telefon,
                'epost_adress' => $this->epost_adress,
                'bolagsengagemang' => $this->bolagsengagemang,
            ],
            'property' => [
                'agandeform' => $this->agandeform,
                'bostadstyp' => $this->bostadstyp,
                'boarea' => $this->boarea,
                'byggar' => $this->byggar,
                'fastighet' => $this->fastighet,
                'personer' => $this->personer,
                'foretag' => $this->foretag,
                'grannar' => $this->grannar,
                'fordon' => $this->fordon,
                'hundar' => $this->hundar,
            ],
            'is_active' => $this->is_active,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
