<?php

use App\Http\Controllers\InternalReportingController;
use App\Http\Controllers\Internal\MailPreviewController;
use App\Http\Controllers\Internal\InternalExportEventSearch;
use App\Http\Controllers\Internal\InternalEventSearchController;

Route::prefix('internal')->middleware(['super_admin', 'auth'])->name('internal.')->group(function () {
    Route::get('reporting', InternalReportingController::class)->name('reporting');
    Route::get('event-search', InternalEventSearchController::class)->name('event-search');
    Route::get('event-search/export', InternalExportEventSearch::class)->name('event-search.export');

    Route::prefix('mail-previews')->name('mail-previews.')->group(function () {
        Route::get('/', [MailPreviewController::class, 'index'])->name('index');
        Route::get('{preview}', [MailPreviewController::class, 'show'])->name('show');
    });
});
