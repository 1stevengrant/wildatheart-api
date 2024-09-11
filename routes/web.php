<?php

use App\Http\Controllers\Attendee\ConnectWithHostController;
use App\Http\Controllers\Auth\OneTimeLoginController;
use App\Http\Controllers\Auth\SuccessAuth0CallbackController;
use App\Http\Controllers\CreateSupportTicketController;
use App\Http\Controllers\DrupalUrlController;
use App\Http\Controllers\Event\ViewEventListingController;
use App\Http\Controllers\HandleMonthlyHostFireEmailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SupportRequestController;
use App\Http\Controllers\Webhooks\SendGridWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::post('support', SupportRequestController::class)->name('support.store');
Route::get('support', CreateSupportTicketController::class)->name('support.create');

include 'auth.php';
include 'emails.php';

Route::get('attend/{event}', ViewEventListingController::class)->name('view.event');
Route::get('connect/{event}', [ConnectWithHostController::class, 'index'])->name('connect.index');
Route::post('connect/{event}', [ConnectWithHostController::class, 'store'])->name('connect.store');

Route::get('onetime/{token}', OneTimeLoginController::class)->name('onetime.login');
Route::get('successful', SuccessAuth0CallbackController::class)->name('successful-auth0-callback');

Route::name('webhooks.')->prefix('webhooks')->group(function () {
    Route::post('sendgrid', SendGridWebhookController::class)->name('sendgrid');
});

Route::prefix('fire/{event}')
    ->middleware(['signed'])
    ->name('fire.')
    ->group(function () {
        Route::get('extinguish', [HandleMonthlyHostFireEmailController::class, 'show'])->name('extinguish');
        Route::post('extinguish', [HandleMonthlyHostFireEmailController::class, 'destroy'])->name('extinguish.destroy');
        Route::get('burn', [HandleMonthlyHostFireEmailController::class, 'update'])->name('burn');
    });

Route::get('drupal/{any}', [DrupalUrlController::class, 'index'])->name('drupal.any');
Route::get('drupal', [DrupalUrlController::class, 'index'])->name('drupal.index');
Route::get('drupal/{type}/{legacyDrupalUrl}', [DrupalUrlController::class, 'redirect'])->name('drupal.redirect');
