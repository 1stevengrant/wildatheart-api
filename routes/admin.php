<?php

use App\Http\Controllers\InternalReportingController;

Route::prefix('internal')->middleware(['super_admin', 'auth'])->name('internal.')->group(function () {
    Route::get('reporting', InternalReportingController::class)->name('reporting');
});
