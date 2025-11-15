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
                'street' => $this->gatuadress,
                'postal_code' => $this->postnummer,
                'city' => $this->postort,
                'parish' => $this->forsamling,
                'municipality' => $this->kommun,
                'state' => $this->lan,
                'fastighet' => $this->fastighet,
                'longitude' => $this->longitude,
                'latitude' => $this->latitud,
            ],
            'person' => [
                // prefer ps_* fields when present because some legacy clients write there
                'first_name' => $this->ps_fornamn ?? $this->fornamn,
                'last_name' => $this->efternamn,
                'full_name' => $this->personnamn,
                'social_security_number' => $this->personnummer,
                'date_of_birth' => $this->fodelsedag?->format('Y-m-d'),
                'age' => $this->alder,
                'sex' => $this->kon,
                'marital_status' => $this->civilstand,
                'phone_numbers' => $this->telefon ?? [],
                'email_addresses' => $this->epost_adress ?? [],
                'corporate_commitments' => $this->bolagsengagemang ?? [],
            ],
            'property' => [
                'ownership_form' => $this->agandeform,
                'housing_type' => $this->bostadstyp,
                'living_area' => $this->boarea,
                'year_of_construction' => $this->byggar,
                'persons' => $this->personer ?? [],
                'companies' => $this->foretag ?? [],
                'neighbors' => $this->grannar ?? [],
                'vehicles' => $this->fordon ?? [],
                'dogs' => $this->hundar ?? [],
            ],
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
