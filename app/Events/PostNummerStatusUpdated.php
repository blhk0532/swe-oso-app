<?php

namespace App\Events;

use App\Models\PostNummer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostNummerStatusUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public PostNummer $postNummer) {}
}
