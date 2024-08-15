<?php

namespace RFlatti\ShopifyApp\Traits;

use RFlatti\ShopifyApp\Models\Store;

trait FunctionTrait
{
    public function getStoreByDomain(string $shop){
        return Store::where('myshopify_domain', $shop)->first();
    }
}
