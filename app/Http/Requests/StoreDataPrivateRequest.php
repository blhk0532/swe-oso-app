<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDataPrivateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bo_gatuadress' => ['nullable', 'string', 'max:65535'],
            'bo_postnummer' => ['nullable', 'string', 'max:255'],
            'bo_postort' => ['nullable', 'string', 'max:255'],
            'bo_forsamling' => ['nullable', 'string', 'max:255'],
            'bo_kommun' => ['nullable', 'string', 'max:255'],
            'bo_lan' => ['nullable', 'string', 'max:255'],
            'ps_fodelsedag' => ['nullable', 'date'],
            'ps_personnummer' => ['nullable', 'string', 'max:255'],
            'ps_alder' => ['nullable', 'string', 'max:255'],
            'ps_kon' => ['nullable', 'string', 'max:255', Rule::in(['M', 'F', 'O'])],
            'ps_civilstand' => ['nullable', 'string', 'max:255'],
            'ps_fornamn' => ['nullable', 'string', 'max:255'],
            'ps_efternamn' => ['nullable', 'string', 'max:255'],
            'ps_personnamn' => ['nullable', 'string', 'max:65535'],
            'ps_telefon' => ['nullable', 'array'],
            'ps_telefon.*' => ['nullable', 'string'],
            'ps_epost_adress' => ['nullable', 'array'],
            'ps_epost_adress.*' => ['nullable', 'email'],
            'ps_bolagsengagemang' => ['nullable', 'array'],
            'bo_agandeform' => ['nullable', 'string', 'max:255'],
            'bo_bostadstyp' => ['nullable', 'string', 'max:255'],
            'bo_boarea' => ['nullable', 'string', 'max:255'],
            'bo_byggar' => ['nullable', 'string', 'max:255'],
            'bo_fastighet' => ['nullable', 'string', 'max:255'],
            'bo_personer' => ['nullable', 'array'],
            'bo_personer.*' => ['nullable', 'string'],
            'bo_foretag' => ['nullable', 'array'],
            'bo_foretag.*' => ['nullable', 'string'],
            'bo_grannar' => ['nullable', 'array'],
            'bo_grannar.*' => ['nullable', 'string'],
            'bo_fordon' => ['nullable', 'array'],
            'bo_hundar' => ['nullable', 'array'],
            'bo_hundar.*' => ['nullable', 'string'],
            'bo_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'bo_latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
