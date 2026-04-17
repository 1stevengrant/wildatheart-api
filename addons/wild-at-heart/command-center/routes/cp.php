<?php

use Illuminate\Support\Facades\Route;
use WildAtHeart\CommandCenter\Http\Controllers\CP\CommandCenterController;

Route::name('command-center.')->prefix('command-center')->group(function () {
    Route::get('/', [CommandCenterController::class, 'index'])->name('index');
    Route::post('/features/toggle', [CommandCenterController::class, 'toggle'])->name('features.toggle');
    Route::post('/astro-build', [CommandCenterController::class, 'triggerAstroBuild'])->name('astro-build.trigger');
});
