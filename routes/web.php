<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DrupalUrlController;
use App\Http\Controllers\FiresPageController;
use App\Http\Controllers\SupportRequestController;
use App\Http\Controllers\UpcomingEventsController;
use App\Http\Controllers\CreateSupportTicketController;
use App\Http\Controllers\Event\ViewEventListingController;
use App\Http\Controllers\Attendee\ConnectWithHostController;
use App\Http\Controllers\User\CompleteUserProfileController;
use App\Http\Controllers\Webhooks\SendGridWebhookController;
use App\Http\Controllers\HandleMonthlyHostFireEmailController;
use App\Http\Controllers\User\IncompleteUserProfileController;
use App\Http\Controllers\Webhooks\SalesforceWebhookController;

Route::get('/', HomeController::class)->name('home');

Route::prefix('support')->name('support.')->group(function () {
    Route::post('/', SupportRequestController::class)->name('store');
    Route::get('/', CreateSupportTicketController::class)->name('create');
});

include 'auth.php';
include 'admin.php';

Route::get('attend/{event}', ViewEventListingController::class)->name('view.event');

Route::prefix('connect')->name('connect.')->group(function () {
    Route::get('{event}', [ConnectWithHostController::class, 'index'])->name('index');
    Route::post('{event}', [ConnectWithHostController::class, 'store'])->name('store');
});

Route::name('webhooks.')->prefix('webhooks')->group(function () {
    Route::post('sendgrid', SendGridWebhookController::class)->name('sendgrid');
    Route::post('salesforce', SalesforceWebhookController::class)->name('salesforce');
});

Route::prefix('fire/{event}')
    ->name('fire.')
    ->middleware(['signed'])
    ->group(function () {
        Route::get('extinguish', [HandleMonthlyHostFireEmailController::class, 'show'])->name('extinguish');
        Route::post('extinguish', [HandleMonthlyHostFireEmailController::class, 'destroy'])->name('extinguish.destroy');
        Route::get('burn', [HandleMonthlyHostFireEmailController::class, 'update'])->name('burn');
    });

Route::prefix('drupal')->name('drupal.')->group(function () {
    Route::get('courses/{course}', [DrupalUrlController::class, 'courses'])->name('course');
    Route::get('{any}', [DrupalUrlController::class, 'any'])
        ->where('any', '.*')->name('drupal.any');
    Route::get('/', [DrupalUrlController::class, 'index'])->name('index');
});

Route::name('upcoming.')->group(function () {
    Route::get('upcoming', UpcomingEventsController::class)->name('events');
    Route::get('fires', FiresPageController::class)->name('fires');
});

Route::prefix('users')->name('users.')->group(function () {
    Route::get('incomplete', IncompleteUserProfileController::class)->name('incomplete');
    Route::get('complete', CompleteUserProfileController::class)->name('complete');
});
