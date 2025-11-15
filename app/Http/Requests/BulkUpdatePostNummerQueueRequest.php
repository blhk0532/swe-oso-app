<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdatePostNummerQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'records' => 'required|array|min:1|max:50',
            'records.*.post_nummer' => 'required|string|max:10',
            'records.*.post_ort' => 'nullable|string',
            'records.*.post_lan' => 'nullable|string',
            'records.*.merinfo_personer_saved' => 'sometimes|integer|min:0',
            'records.*.merinfo_foretag_saved' => 'sometimes|integer|min:0',
            'records.*.merinfo_personer_total' => 'sometimes|integer|min:0',
            'records.*.merinfo_foretag_total' => 'sometimes|integer|min:0',
            'records.*.merinfo_queued' => 'sometimes|boolean',
            'records.*.merinfo_scraped' => 'sometimes|boolean',
            'records.*.merinfo_complete' => 'sometimes|boolean',
            'records.*.ratsit_personer_saved' => 'sometimes|integer|min:0',
            'records.*.ratsit_foretag_saved' => 'sometimes|integer|min:0',
            'records.*.ratsit_personer_total' => 'sometimes|integer|min:0',
            'records.*.ratsit_foretag_total' => 'sometimes|integer|min:0',
            'records.*.ratsit_queued' => 'sometimes|boolean',
            'records.*.ratsit_scraped' => 'sometimes|boolean',
            'records.*.ratsit_complete' => 'sometimes|boolean',
            'records.*.hitta_personer_saved' => 'sometimes|integer|min:0',
            'records.*.hitta_foretag_saved' => 'sometimes|integer|min:0',
            'records.*.hitta_personer_total' => 'sometimes|integer|min:0',
            'records.*.hitta_foretag_total' => 'sometimes|integer|min:0',
            'records.*.hitta_queued' => 'sometimes|boolean',
            'records.*.hitta_scraped' => 'sometimes|boolean',
            'records.*.hitta_complete' => 'sometimes|boolean',
            'records.*.post_nummer_personer_saved' => 'sometimes|integer|min:0',
            'records.*.post_nummer_foretag_saved' => 'sometimes|integer|min:0',
            'records.*.post_nummer_personer_total' => 'sometimes|integer|min:0',
            'records.*.post_nummer_foretag_total' => 'sometimes|integer|min:0',
            'records.*.post_nummer_queued' => 'sometimes|boolean',
            'records.*.post_nummer_scraped' => 'sometimes|boolean',
            'records.*.post_nummer_complete' => 'sometimes|boolean',
            'records.*.is_active' => 'sometimes|boolean',
        ];
    }
}
