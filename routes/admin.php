<?php

use App\Http\Controllers\InternalReportingController;
use App\Http\Controllers\Internal\AiAssistantController;
use App\Http\Controllers\Internal\InternalExportEventSearch;
use App\Http\Controllers\Internal\InternalEventSearchController;

Route::prefix('internal')->middleware(['super_admin', 'auth'])->name('internal.')->group(function () {
    Route::get('reporting', InternalReportingController::class)->name('reporting');
    Route::get('event-search', InternalEventSearchController::class)->name('event-search');
    Route::get('event-search/export', InternalExportEventSearch::class)->name('event-search.export');

    Route::get('ai-assistant', [AiAssistantController::class, 'index'])->name('ai-assistant');
    Route::post('ai-assistant/chat', [AiAssistantController::class, 'chat'])->name('ai-assistant.chat');
    Route::get('ai-assistant/conversations/{conversationId}', [AiAssistantController::class, 'conversation'])->name('ai-assistant.conversation');
});
