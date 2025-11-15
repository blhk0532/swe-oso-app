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
            'gatuadress' => ['nullable', 'string', 'max:65535'],
            'postnummer' => ['nullable', 'string', 'max:255'],
            // 'bo_' legacy fields
            'bo_gatuadress' => ['nullable', 'string', 'max:65535'],
            'bo_postnummer' => ['nullable', 'string', 'max:255'],
            'bo_postort' => ['nullable', 'string', 'max:255'],
            'bo_forsamling' => ['nullable', 'string', 'max:255'],
            'bo_kommun' => ['nullable', 'string', 'max:255'],
            'bo_lan' => ['nullable', 'string', 'max:255'],
            'postort' => ['nullable', 'string', 'max:255'],
            'forsamling' => ['nullable', 'string', 'max:255'],
            'kommun' => ['nullable', 'string', 'max:255'],
            'lan' => ['nullable', 'string', 'max:255'],
            'fodelsedag' => ['nullable', 'date'],
            'personnummer' => ['nullable', 'string', 'max:255'],
            'alder' => ['nullable', 'string', 'max:255'],
            'kon' => ['nullable', 'string', 'max:255', Rule::in(['M', 'F', 'O'])],
            // legacy / prefixed variants
            'ps_kon' => ['nullable', 'string', 'max:255', Rule::in(['M', 'F', 'O'])],
            'civilstand' => ['nullable', 'string', 'max:255'],
            'fornamn' => ['nullable', 'string', 'max:255'],
            'ps_fornamn' => ['nullable', 'string', 'max:255'],
            'efternamn' => ['nullable', 'string', 'max:255'],
            'ps_efternamn' => ['nullable', 'string', 'max:255'],
            'personnamn' => ['nullable', 'string', 'max:65535'],
            'ps_personnamn' => ['nullable', 'string', 'max:65535'],
            'telefon' => ['nullable', 'array'],
            'telefon.*' => ['nullable', 'string'],
            'ps_telefon' => ['nullable', 'array'],
            'ps_telefon.*' => ['nullable', 'string'],
            'epost_adress' => ['nullable', 'array'],
            'epost_adress.*' => ['nullable', 'email'],
            'ps_epost_adress' => ['nullable', 'array'],
            'ps_epost_adress.*' => ['nullable', 'email'],
            'bolagsengagemang' => ['nullable', 'array'],
            'agandeform' => ['nullable', 'string', 'max:255'],
            'bostadstyp' => ['nullable', 'string', 'max:255'],
            'boarea' => ['nullable', 'string', 'max:255'],
            'byggar' => ['nullable', 'string', 'max:255'],
            'fastighet' => ['nullable', 'string', 'max:255'],
            'personer' => ['nullable', 'array'],
            'personer.*' => ['nullable', 'string'],
            'foretag' => ['nullable', 'array'],
            'foretag.*' => ['nullable', 'string'],
            'grannar' => ['nullable', 'array'],
            'grannar.*' => ['nullable', 'string'],
            'fordon' => ['nullable', 'array'],
            'hundar' => ['nullable', 'array'],
            'hundar.*' => ['nullable', 'string'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
