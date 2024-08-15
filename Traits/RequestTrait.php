<?php

namespace RFlatti\ShopifyApp\Traits;

use GuzzleHttp\Client;

trait RequestTrait
{
    public function makeAnAPICallToShopify($method = 'GET', $endpoint, $url_params = null, array $headers, $requestBody = null){
        //Headers
        /*
         * Content Type: application/json
         * X-Shopify-Access-Token: value
         */
        try {
            $client = new Client();
            $response = null;
            switch ($method){
                case 'GET': $response = $client->request($method, $endpoint, ['headers' => $headers]); break;
                case 'POST': $response = $client->request($method, $endpoint, [
                    'headers' => $headers,
                    'json' => $requestBody
                ]); break;

            }
            return [
                'statusCode' => $response->getStatusCode(),
                'body' => $response->getBody()
            ];
        } catch (\Exception $e){
            return [
                'statusCode' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => null
            ];
        }
    }
}
