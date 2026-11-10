<?php

use App\Http\Controllers\InternalReportingController;
use App\Http\Controllers\Internal\MailPreviewController;
use App\Http\Controllers\Internal\FireSchedulesController;
use App\Http\Controllers\Internal\InternalExportEventSearch;
use App\Http\Controllers\Internal\FireScheduleMovesController;
use App\Http\Controllers\Internal\FireScheduleChecksController;
use App\Http\Controllers\Internal\InternalEventSearchController;
use App\Http\Controllers\Internal\FireScheduleNotificationsController;

Route::prefix('internal')->middleware(['super_admin', 'auth'])->name('internal.')->group(function () {
    Route::get('reporting', InternalReportingController::class)->name('reporting');
    Route::get('event-search', InternalEventSearchController::class)->name('event-search');
    Route::get('event-search/export', InternalExportEventSearch::class)->name('event-search.export');

    Route::prefix('mail-previews')->name('mail-previews.')->group(function () {
        Route::get('/', [MailPreviewController::class, 'index'])->name('index');
        Route::get('{preview}', [MailPreviewController::class, 'show'])->name('show');
    });

    Route::prefix('fire-schedules')->name('fire-schedules.')->middleware('not_production')->group(function () {
        Route::get('/', [FireSchedulesController::class, 'index'])->name('index');
        Route::post('{event}/moves', [FireScheduleMovesController::class, 'store'])->name('moves.store');
        Route::post('{event}/checks', [FireScheduleChecksController::class, 'store'])->name('checks.store');
        Route::delete('{event}/notifications', [FireScheduleNotificationsController::class, 'destroy'])->name('notifications.destroy');
    });
});
