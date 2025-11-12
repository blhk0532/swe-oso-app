<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEniroDataRequest extends FormRequest
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
        return [
            'personnamn' => ['nullable', 'string', 'max:65535'],
            'alder' => ['nullable', 'string', 'max:255'],
            'kon' => ['nullable', 'string', 'max:255', Rule::in(['M', 'F', 'O'])],
            'gatuadress' => ['nullable', 'string', 'max:65535'],
            'postnummer' => ['nullable', 'string', 'max:255'],
            'postort' => ['nullable', 'string', 'max:255'],
            'telefon' => ['nullable', 'string', 'max:255'],
            'karta' => ['nullable', 'string', 'max:65535'],
            'link' => ['nullable', 'string', 'max:65535'],
            'bostadstyp' => ['nullable', 'string', 'max:255'],
            'bostadspris' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_telefon' => ['nullable', 'boolean'],
            'is_ratsit' => ['nullable', 'boolean'],
        ];
    }
}
