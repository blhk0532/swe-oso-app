<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostNummerQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'post_ort' => 'nullable|string',
            'post_lan' => 'nullable|string',
            'merinfo_personer_saved' => 'sometimes|integer|min:0',
            'merinfo_foretag_saved' => 'sometimes|integer|min:0',
            'merinfo_personer_total' => 'sometimes|integer|min:0',
            'merinfo_foretag_total' => 'sometimes|integer|min:0',
            'merinfo_queued' => 'sometimes|boolean',
            'merinfo_scraped' => 'sometimes|boolean',
            'merinfo_complete' => 'sometimes|boolean',
            'ratsit_personer_saved' => 'sometimes|integer|min:0',
            'ratsit_foretag_saved' => 'sometimes|integer|min:0',
            'ratsit_personer_total' => 'sometimes|integer|min:0',
            'ratsit_foretag_total' => 'sometimes|integer|min:0',
            'ratsit_queued' => 'sometimes|boolean',
            'ratsit_scraped' => 'sometimes|boolean',
            'ratsit_complete' => 'sometimes|boolean',
            'hitta_personer_saved' => 'sometimes|integer|min:0',
            'hitta_foretag_saved' => 'sometimes|integer|min:0',
            'hitta_personer_total' => 'sometimes|integer|min:0',
            'hitta_foretag_total' => 'sometimes|integer|min:0',
            'hitta_queued' => 'sometimes|boolean',
            'hitta_scraped' => 'sometimes|boolean',
            'hitta_complete' => 'sometimes|boolean',
            'post_nummer_personer_saved' => 'sometimes|integer|min:0',
            'post_nummer_foretag_saved' => 'sometimes|integer|min:0',
            'post_nummer_personer_total' => 'sometimes|integer|min:0',
            'post_nummer_foretag_total' => 'sometimes|integer|min:0',
            'post_nummer_queued' => 'sometimes|boolean',
            'post_nummer_scraped' => 'sometimes|boolean',
            'post_nummer_complete' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
