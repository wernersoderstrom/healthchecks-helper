<?php

// config for Lasselehtinen/HealthchecksHelper
return [

    /*
     * When disabled, the healthchecks() macro registers no pings. Handy for
     * local and testing environments.
     */
    'enabled' => env('HEALTHCHECKS_ENABLED', true),

    /*
     * The base URL of the ping endpoint. Change this when you self-host
     * Healthchecks.
     */
    'ping_url' => env('HEALTHCHECKS_PING_URL', 'https://hc-ping.com'),

    /*
     * The project's ping key. Only needed when you identify checks by slug
     * instead of UUID.
     */
    'ping_key' => env('HEALTHCHECKS_PING_KEY'),

];
