<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMerinfoQueueRequest extends FormRequest
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
            'foretag_total' => 'sometimes|integer|min:0',
            'personer_total' => 'sometimes|integer|min:0',
            'personer_house' => 'sometimes|integer|min:0',
            'foretag_phone' => 'sometimes|integer|min:0',
            'personer_phone' => 'sometimes|integer|min:0',
            'foretag_saved' => 'sometimes|integer|min:0',
            'personer_saved' => 'sometimes|integer|min:0',
            'foretag_queued' => 'sometimes|integer|min:0',
            'personer_queued' => 'sometimes|integer|min:0',
            'foretag_scraped' => 'sometimes|boolean',
            'personer_scraped' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
