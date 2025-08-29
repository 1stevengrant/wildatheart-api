<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V2\Navs\MobileNavController;
use App\Http\Controllers\API\V2\Navs\DesktopNavController;

Route::prefix('navs')->name('navs.')->group(function () {
    Route::get('desktop', DesktopNavController::class)->name('desktop');
    Route::get('mobile', MobileNavController::class)->name('mobile');
});
