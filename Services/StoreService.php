<?php

namespace RFlatti\ShopifyApp\Services;

use RFlatti\ShopifyApp\Services\StorageService;

class StoreService
{
    public function __construct(
        protected StorageService $storageService,
    ){}

    public function getStore(){
        return $this->storageService->getIds();

    }
}
