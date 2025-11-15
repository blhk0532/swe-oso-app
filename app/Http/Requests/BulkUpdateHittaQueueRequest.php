<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateHittaQueueRequest extends FormRequest
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
            'records.*.foretag_total' => 'sometimes|integer|min:0',
            'records.*.personer_total' => 'sometimes|integer|min:0',
            'records.*.personer_house' => 'sometimes|integer|min:0',
            'records.*.foretag_phone' => 'sometimes|integer|min:0',
            'records.*.personer_phone' => 'sometimes|integer|min:0',
            'records.*.foretag_saved' => 'sometimes|integer|min:0',
            'records.*.personer_saved' => 'sometimes|integer|min:0',
            'records.*.foretag_queued' => 'sometimes|integer|min:0',
            'records.*.personer_queued' => 'sometimes|integer|min:0',
            'records.*.foretag_scraped' => 'sometimes|boolean',
            'records.*.personer_scraped' => 'sometimes|boolean',
            'records.*.is_active' => 'sometimes|boolean',
        ];
    }
}
