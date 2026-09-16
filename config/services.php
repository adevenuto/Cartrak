<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
     * NHTSA vPIC and recalls. Both are free, keyless and US-only, and both are
     * strictly enrichment — the app stays fully usable with them unreachable.
     */
    'nhtsa' => [
        'vpic_url' => env('NHTSA_VPIC_URL', 'https://vpic.nhtsa.dot.gov/api/vehicles'),
        'recalls_url' => env('NHTSA_RECALLS_URL', 'https://api.nhtsa.gov'),
        // vPIC routinely takes 2-5s, hence the queued job rather than a request.
        'timeout' => (int) env('NHTSA_TIMEOUT', 15),
    ],

    /*
     * EPA fuel economy, used to benchmark real computed MPG against the sticker.
     */
    'fueleconomy' => [
        'url' => env('FUELECONOMY_URL', 'https://www.fueleconomy.gov/ws/rest'),
        'timeout' => (int) env('FUELECONOMY_TIMEOUT', 10),
    ],

];
