<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRatsitDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Same as store, but all fields optional
        return [
            'bo_gatuadress' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'bo_postnummer' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bo_postort' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bo_forsamling' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bo_kommun' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bo_lan' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ps_fodelsedag' => ['sometimes', 'nullable', 'date'],
            'ps_personnummer' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ps_alder' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ps_kon' => ['sometimes', 'nullable', 'string', 'max:255', Rule::in(['M', 'F', 'O'])],
            'ps_civilstand' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ps_fornamn' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ps_efternamn' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ps_personnamn' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'ps_telefon' => ['sometimes', 'nullable', 'array'],
            'ps_telefon.*' => ['nullable', 'string'],
            'ps_epost_adress' => ['sometimes', 'nullable', 'array'],
            'ps_epost_adress.*' => ['nullable', 'email'],
            'ps_bolagsengagemang' => ['sometimes', 'nullable', 'array'],
            'bo_agandeform' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bo_bostadstyp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bo_boarea' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bo_byggar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bo_fastighet' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bo_personer' => ['sometimes', 'nullable', 'array'],
            'bo_personer.*' => ['nullable', 'string'],
            'bo_foretag' => ['sometimes', 'nullable', 'array'],
            'bo_foretag.*' => ['nullable', 'string'],
            'bo_grannar' => ['sometimes', 'nullable', 'array'],
            'bo_grannar.*' => ['nullable', 'string'],
            'bo_fordon' => ['sometimes', 'nullable', 'array'],
            'bo_hundar' => ['sometimes', 'nullable', 'array'],
            'bo_hundar.*' => ['nullable', 'string'],
            'bo_longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'bo_latitud' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
