<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MerinfoQueueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_nummer' => $this->post_nummer,
            'post_ort' => $this->post_ort,
            'post_lan' => $this->post_lan,
            'foretag_total' => $this->foretag_total,
            'personer_total' => $this->personer_total,
            'personer_house' => $this->personer_house,
            'personer_saved' => $this->personer_saved,
            'personer_queued' => $this->personer_queued,
            'personer_scraped' => $this->personer_scraped,
            'foretag_saved' => $this->foretag_saved,
            'foretag_queued' => $this->foretag_queued,
            'foretag_scraped' => $this->foretag_scraped,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
