<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HittaQueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_nummer' => $this->post_nummer,
            'post_ort' => $this->post_ort,
            'post_lan' => $this->post_lan,
            'foretag_total' => $this->foretag_total,
            'personer_total' => $this->personer_total,
            'personer_saved' => $this->personer_saved,
            'personer_queued' => $this->personer_queued,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
