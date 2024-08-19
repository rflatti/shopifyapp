<?php

namespace RFlatti\ShopifyApp;

use Illuminate\Support\ServiceProvider;
use RFlatti\ShopifyApp\Services\StorageService;

class PackageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/Setup/config/shopify.php' => config_path('shopify.php'),
        ], 'config');
        $this->publishes([
            __DIR__.'/Setup/public/css/global.css' => public_path('css/global.css'),
        ],'config');
        $this->publishes([
            __DIR__.'/Setup/database/migrations/2024_08_14_175333_create_stores_table.php' => database_path('migrations/2024_08_14_175333_create_stores_table.php'),
        ],'config');
        $this->loadRoutesFrom(__DIR__.'/Setup/routes/shopify.php');

        $this->publishes([
            __DIR__.'/Setup/Controllers/RenderShopifyApp.php' => app_path('Http/Controllers/RenderShopifyApp.php'),
        ], 'config');
        $this->publishes([
            __DIR__.'/Setup/views' => resource_path('views'),
        ], 'config');

    }

    public function register()
    {
    }
}
