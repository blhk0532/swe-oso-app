<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataPrivateResource extends JsonResource
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
                'street' => $this->bo_gatuadress,
                'postal_code' => $this->bo_postnummer,
                'city' => $this->bo_postort,
                'parish' => $this->bo_forsamling,
                'municipality' => $this->bo_kommun,
                'state' => $this->bo_lan,
                'fastighet' => $this->bo_fastighet,
                'longitude' => $this->bo_longitude,
                'latitude' => $this->bo_latitud,
            ],
            'person' => [
                'first_name' => $this->ps_fornamn,
                'last_name' => $this->ps_efternamn,
                'full_name' => $this->ps_personnamn,
                'social_security_number' => $this->ps_personnummer,
                'date_of_birth' => $this->ps_fodelsedag?->format('Y-m-d'),
                'age' => $this->ps_alder,
                'sex' => $this->ps_kon,
                'marital_status' => $this->ps_civilstand,
                'phone_numbers' => $this->ps_telefon ?? [],
                'email_addresses' => $this->ps_epost_adress ?? [],
                'corporate_commitments' => $this->ps_bolagsengagemang ?? [],
            ],
            'property' => [
                'ownership_form' => $this->bo_agandeform,
                'housing_type' => $this->bo_bostadstyp,
                'living_area' => $this->bo_boarea,
                'year_of_construction' => $this->bo_byggar,
                'persons' => $this->bo_personer ?? [],
                'companies' => $this->bo_foretag ?? [],
                'neighbors' => $this->bo_grannar ?? [],
                'vehicles' => $this->bo_fordon ?? [],
                'dogs' => $this->bo_hundar ?? [],
            ],
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
