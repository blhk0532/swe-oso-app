<?php

namespace App\Events;

use App\Models\PostNummer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostNummerStatusUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public PostNummer $postNummer) {}

    public function broadcastOn(): Channel
    {
        // Private channel could be used if auth-scoped; using public for simplicity
        return new Channel('postnummer.status');
    }

    public function broadcastAs(): string
    {
        return 'PostNummerStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'post_nummer' => $this->postNummer->post_nummer,
            'status' => $this->postNummer->status,
            'progress' => $this->postNummer->progress,
            'total_count' => $this->postNummer->total_count,
        ];
    }
}
