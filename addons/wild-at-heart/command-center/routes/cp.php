<?php

use Illuminate\Support\Facades\Route;
use WildAtHeart\CommandCenter\Http\Controllers\CP\AskMessagesController;
use WildAtHeart\CommandCenter\Http\Controllers\CP\CommandCenterController;
use WildAtHeart\CommandCenter\Http\Controllers\CP\AskConversationsController;

Route::name('command-center.')->prefix('command-center')->group(function () {
    Route::get('/', [CommandCenterController::class, 'index'])->name('index');
    Route::post('/features/toggle', [CommandCenterController::class, 'toggle'])->name('features.toggle');
    Route::post('/astro-build', [CommandCenterController::class, 'triggerAstroBuild'])->name('astro-build.trigger');

    Route::name('ask.')->prefix('ask')->middleware('can:usePlatformAnalyst')->group(function () {
        Route::get('/', [AskConversationsController::class, 'index'])->name('index');
        Route::get('/conversations/{conversationId}', [AskConversationsController::class, 'show'])->name('conversations.show');
        Route::post('/messages', [AskMessagesController::class, 'store'])->middleware('throttle:ask-platform')->name('messages.store');
    });
});
