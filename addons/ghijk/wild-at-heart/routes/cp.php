<?php

use Illuminate\Support\Facades\Route;
use Ghijk\WildAtHeart\Http\Controllers\CP\WildAtHeartController;

Route::name('wild-at-heart.')->prefix('wild-at-heart')->group(function () {
    Route::get('/', [WildAtHeartController::class, 'index'])->name('index');
});
