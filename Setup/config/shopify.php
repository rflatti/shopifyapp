<?php

return [
    'shopify_api_key' => env('SHOPIFY_API_KEY', '<YOUR_APP_API_KEY>'),
    'shopify_api_secret' => env('SHOPIFY_API_SECRET', '<YOUR_APP_API_SECRET>'),
    'shopify_api_version' => '2023-10', //or other api versions for shopify
    'api_scopes' => '<SCOPES_THAT_THE_APP_NEEDS>',
];
