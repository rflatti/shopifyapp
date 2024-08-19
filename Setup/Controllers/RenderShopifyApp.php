<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use RFlatti\ShopifyApp\Services\StorageService;

class RenderShopifyApp
{
    public function __construct(
        protected StorageService $storageService,
    ){}

    public function execute($storeDetails, $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    {

        return view('pages.home', ['store_id' => $this->storageService->getIds()[0]]);
    }
}
