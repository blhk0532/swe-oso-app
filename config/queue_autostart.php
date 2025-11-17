<?php

return [
    /**
     * Enable or disable automatic queue worker autostart for the Filament UI.
     * Set this to false to stop the admin panel from starting background workers.
     */
    'enabled' => env('QUEUE_AUTOSTART', true),

    /**
     * List of queue names which the UI may automatically start workers for.
     * Use environment variables to change the default from the `.env` file.
     */
    'queues' => array_filter(explode(',', env('QUEUE_AUTOSTART_QUEUES', 'filament,ratsit,merinfo'))),
];
