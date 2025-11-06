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
                'gatuadress' => $this->bo_gatuadress,
                'postnummer' => $this->bo_postnummer,
                'postort' => $this->bo_postort,
                'forsamling' => $this->bo_forsamling,
                'kommun' => $this->bo_kommun,
                'lan' => $this->bo_lan,
                'longitude' => $this->bo_longitude,
                'latitud' => $this->bo_latitud,
            ],
            'person' => [
                'fodelsedag' => optional($this->ps_fodelsedag)->format('Y-m-d'),
                'personnummer' => $this->ps_personnummer,
                'alder' => $this->ps_alder,
                'kon' => $this->ps_kon,
                'civilstand' => $this->ps_civilstand,
                'fornamn' => $this->ps_fornamn,
                'efternamn' => $this->ps_efternamn,
                'personnamn' => $this->ps_personnamn,
                'telefon' => $this->ps_telefon,
                'epost_adress' => $this->ps_epost_adress,
                'bolagsengagemang' => $this->ps_bolagsengagemang,
            ],
            'property' => [
                'agandeform' => $this->bo_agandeform,
                'bostadstyp' => $this->bo_bostadstyp,
                'boarea' => $this->bo_boarea,
                'byggar' => $this->bo_byggar,
                'fastighet' => $this->bo_fastighet,
                'personer' => $this->bo_personer,
                'foretag' => $this->bo_foretag,
                'grannar' => $this->bo_grannar,
                'fordon' => $this->bo_fordon,
                'hundar' => $this->bo_hundar,
            ],
            'is_active' => $this->is_active,
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
