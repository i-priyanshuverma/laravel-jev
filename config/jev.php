<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TypeSafe Jev API Key
    |--------------------------------------------------------------------------
    |
    | This key is used to authenticate requests to the TypeSafe Jev API.
    | Obtain this key from your TypeSafe account console.
    |
    */
    'api_key' => env('JEV_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base API URL
    |--------------------------------------------------------------------------
    |
    | The base URL endpoint for TypeSafe Jev API.
    |
    */
    'base_url' => env('JEV_BASE_URL', 'https://api.typesafe.ai/v1'),

    /*
    |--------------------------------------------------------------------------
    | Default Confidence Threshold
    |--------------------------------------------------------------------------
    |
    | When running boolean checks like Jev::is(), results with confidence
    | below this threshold will be treated as false.
    | Range: 0.00 to 1.00 (default: 0.80)
    |
    */
    'threshold' => (float) env('JEV_DEFAULT_THRESHOLD', 0.80),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout & Retries
    |--------------------------------------------------------------------------
    |
    | Maximum seconds to wait for a response from the Jev API, and how many
    | times to retry transient network connection issues.
    |
    */
    'timeout' => (int) env('JEV_TIMEOUT', 5),
    'retries' => (int) env('JEV_RETRIES', 2),

    /*
    |--------------------------------------------------------------------------
    | Response Caching
    |--------------------------------------------------------------------------
    |
    | Optionally cache identical decisions to reduce API calls and achieve
    | sub-millisecond local responses.
    |
    */
    'cache' => [
        'enabled' => (bool) env('JEV_CACHE_ENABLED', false),
        'ttl'     => (int) env('JEV_CACHE_TTL', 3600), // In seconds
        'store'   => env('JEV_CACHE_STORE'), // null for default cache driver
    ],

];
