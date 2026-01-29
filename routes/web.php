<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\APIDocsController;
use App\Http\Controllers\DrupalUrlController;
use App\Http\Controllers\FiresPageController;
use App\Http\Controllers\SupportRequestController;
use App\Http\Controllers\UpcomingEventsController;
use App\Http\Controllers\CreateSupportTicketController;
use App\Http\Controllers\Event\ViewEventListingController;
use App\Http\Controllers\Attendee\ConnectWithHostController;
use App\Http\Controllers\Testing\BrowserTestLoginController;
use App\Http\Controllers\Webhooks\SendGridWebhookController;
use App\Http\Controllers\Event\AcceptHostInvitationController;
use App\Http\Controllers\HandleMonthlyHostFireEmailController;
use App\Http\Controllers\Event\ConfirmHostInvitationController;
use App\Http\Controllers\Event\AcceptOwnershipTransferController;
use App\Http\Controllers\Event\ConfirmOwnershipTransferController;

Route::get('/', HomeController::class)->name('home');

// Browser testing login route - only available in local/testing environments
if (app()->environment(['local', 'testing'])) {
    Route::get('/_testing/login', BrowserTestLoginController::class)->name('testing.login');
}

Route::prefix('support')->name('support.')->group(function () {
    Route::post('/', SupportRequestController::class)->name('store');
    Route::get('/', CreateSupportTicketController::class)->name('create');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';

Route::get('attend/{event}', ViewEventListingController::class)->name('view.event');

Route::middleware(['auth', 'agreed_to_host_agreement'])->group(function () {
    Route::get('accept-host-invitation/{invitation}', AcceptHostInvitationController::class)
        ->name('accept.host.invitation');
    Route::post('accept-host-invitation/{invitation}', ConfirmHostInvitationController::class)
        ->name('accept.host.invitation.confirm');

    Route::get('accept-ownership-transfer/{transfer}', AcceptOwnershipTransferController::class)
        ->name('accept.ownership.transfer');
    Route::post('accept-ownership-transfer/{transfer}', ConfirmOwnershipTransferController::class)
        ->name('accept.ownership.transfer.confirm');
});

Route::prefix('connect')->name('connect.')->group(function () {
    Route::get('{event}', [ConnectWithHostController::class, 'index'])->name('index');
    Route::post('{event}', [ConnectWithHostController::class, 'store'])->name('store');
});

Route::name('webhooks.')->prefix('webhooks')->group(function () {
    Route::post('sendgrid', SendGridWebhookController::class)->name('sendgrid');
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

Route::middleware(['super_admin', 'auth'])->group(function () {
    Route::get('/api/documentation', [APIDocsController::class, 'index'])->name('docs.index');
    Route::get('/api/documentation/spec', [APIDocsController::class, 'spec'])->name('docs.spec');
});
