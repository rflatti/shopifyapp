<?php
use Illuminate\Support\Facades\Route;
use RFlatti\ShopifyApp\Controllers\InstallationController;

Route::prefix('shopify')->group(function (){
    Route::get('auth', [InstallationController::class, 'startInstallation']);
    Route::get('auth/redirect', [InstallationController::class, 'handleRedirect'])->name('app_install_redirect');
    Route::get('auth/complete', [InstallationController::class, 'completeInstallation'])->name('app_install_complete');
    Route::get('/', function () {
        return view('welcome');
    })->name('embeded_home');
});
