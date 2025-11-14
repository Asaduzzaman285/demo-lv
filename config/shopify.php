<?php

return [
    'store_url' => env('SHOPIFY_STORE_URL'),
    'api_token' => env('SHOPIFY_API_TOKEN'),
    'api_version' => env('SHOPIFY_API_VERSION', '2025-07'),
    'location_id' => env('SHOPIFY_LOCATION_GID', ''),
];
