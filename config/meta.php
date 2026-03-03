<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meta Platform API (Facebook Messenger + Instagram DMs)
    |--------------------------------------------------------------------------
    |
    | Settings for the Meta Graph API integration.
    | Facebook Messenger and Instagram DMs use the same API.
    |
    */

    'graph_api_version' => env('META_GRAPH_API_VERSION', 'v21.0'),
    'graph_api_url' => 'https://graph.facebook.com',

    /*
    |--------------------------------------------------------------------------
    | App Credentials (from Meta Developer Portal)
    |--------------------------------------------------------------------------
    */
    'app_id' => env('META_APP_ID'),
    'app_secret' => env('META_APP_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */
    'webhook' => [
        'verify_token' => env('META_VERIFY_TOKEN', 'elite-meta-verify-2024'),
        'path' => env('META_WEBHOOK_PATH', 'api/webhooks/meta'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enabled' => true,
        'connection' => null,
        'name' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeouts
    |--------------------------------------------------------------------------
    */
    'timeout' => 30,
    'retry' => [
        'times' => 3,
        'sleep' => 200,
    ],
];
