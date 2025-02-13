<?php

use Illuminate\Support\Facades\Route;
use Ghijk\WildAtHeart\Http\Controllers\Actions\TriggerAstroBuildController;

Route::name('wild-at-heart.')->prefix('wild-at-heart')->group(function () {
    Route::post('/trigger-astro-build', TriggerAstroBuildController::class)->name('trigger-astro-build');
})->middleware('statamic.cp.authenticated');
