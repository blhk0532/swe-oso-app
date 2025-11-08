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
            'gatuadress' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'postnummer' => ['sometimes', 'nullable', 'string', 'max:255'],
            'postort' => ['sometimes', 'nullable', 'string', 'max:255'],
            'forsamling' => ['sometimes', 'nullable', 'string', 'max:255'],
            'kommun' => ['sometimes', 'nullable', 'string', 'max:255'],
            'lan' => ['sometimes', 'nullable', 'string', 'max:255'],
            'fodelsedag' => ['sometimes', 'nullable', 'date'],
            'personnummer' => ['sometimes', 'nullable', 'string', 'max:255'],
            'alder' => ['sometimes', 'nullable', 'string', 'max:255'],
            'kon' => ['sometimes', 'nullable', 'string', 'max:255', Rule::in(['M', 'F', 'O'])],
            'civilstand' => ['sometimes', 'nullable', 'string', 'max:255'],
            'fornamn' => ['sometimes', 'nullable', 'string', 'max:255'],
            'efternamn' => ['sometimes', 'nullable', 'string', 'max:255'],
            'personnamn' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'telefon' => ['sometimes', 'nullable', 'array'],
            'telefon.*' => ['nullable', 'string'],
            'epost_adress' => ['sometimes', 'nullable', 'array'],
            'epost_adress.*' => ['nullable', 'email'],
            'bolagsengagemang' => ['sometimes', 'nullable', 'array'],
            'agandeform' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bostadstyp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'boarea' => ['sometimes', 'nullable', 'string', 'max:255'],
            'byggar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'fastighet' => ['sometimes', 'nullable', 'string', 'max:255'],
            'personer' => ['sometimes', 'nullable', 'array'],
            'personer.*' => ['nullable', 'string'],
            'foretag' => ['sometimes', 'nullable', 'array'],
            'foretag.*' => ['nullable', 'string'],
            'grannar' => ['sometimes', 'nullable', 'array'],
            'grannar.*' => ['nullable', 'string'],
            'fordon' => ['sometimes', 'nullable', 'array'],
            'hundar' => ['sometimes', 'nullable', 'array'],
            'hundar.*' => ['nullable', 'string'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'latitud' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
