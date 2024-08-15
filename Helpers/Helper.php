<?php

if (!function_exists('getShopifyURLForStore')){
    function getShopifyURLForStore($endpoint, $store, $apiVersion = '2023-10'){
        return 'https://'.$store->myshopify_domain.'admin/api/'.config('shopify.shopify_api_version').'/'.$endpoint;
    }
    function getShopifyHeadersForStore($storeDetails){
        return [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $storeDetails['access_token'],
        ];
    }
}
