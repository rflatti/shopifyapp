<?php

return [
    'shopify_api_key' => env('SHOPIFY_API_KEY', '<YOUR_APP_API_KEY>'),
    'shopify_api_secret' => env('SHOPIFY_API_SECRET', '<YOUR_APP_API_SECRET>'),
    'shopify_api_version' => '2023-10', //or other api versions for shopify
    'handle' => '<your-app-handle>',
    'api_scopes' => '<your-app-scopes>',
    'access_token_valid_controller' => [
        'class' => "App\\Http\\Controllers\\RenderShopifyApp" //You can change this if you wand :)
    ],
];
