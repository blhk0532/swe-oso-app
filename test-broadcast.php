<?php

use App\Events\PostNummerStatusUpdated;
use App\Models\PostNummer;

// Get the first PostNummer record
$postNummer = PostNummer::first();

if ($postNummer) {
    echo "Testing broadcast for PostNummer: {$postNummer->post_nummer}\n";

    // Manually trigger the event
    event(new PostNummerStatusUpdated($postNummer));

    echo "Event dispatched! Check your browser console and Reverb logs.\n";
} else {
    echo "No PostNummer records found. Please create one first.\n";
}
