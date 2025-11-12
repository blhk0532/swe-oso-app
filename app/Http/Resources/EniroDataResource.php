<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\EniroData */
class EniroDataResource extends JsonResource
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
            'person' => [
                'personnamn' => $this->personnamn,
                'alder' => $this->alder,
                'kon' => $this->kon,
            ],
            'address' => [
                'gatuadress' => $this->gatuadress,
                'postnummer' => $this->postnummer,
                'postort' => $this->postort,
            ],
            'contact' => [
                'telefon' => $this->telefon,
                'karta' => $this->karta,
                'link' => $this->link,
            ],
            'property' => [
                'bostadstyp' => $this->bostadstyp,
                'bostadspris' => $this->bostadspris,
            ],
            'flags' => [
                'is_active' => $this->is_active,
                'is_telefon' => $this->is_telefon,
                'is_ratsit' => $this->is_ratsit,
            ],
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
