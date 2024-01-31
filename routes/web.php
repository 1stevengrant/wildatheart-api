<?php

use App\Http\Controllers\Attendee\ConnectWithHostController;
use App\Http\Controllers\Attendee\ToggleConnectionContactedController;
use App\Http\Controllers\Auth\OneTimeLoginController;
use App\Http\Controllers\Auth\SuccessAuth0CallbackController;
use App\Http\Controllers\Event\CreateEventController;
use App\Http\Controllers\Event\DeleteEventController;
use App\Http\Controllers\Event\EditEventController;
use App\Http\Controllers\Event\HostEventController;
use App\Http\Controllers\Event\ToggleEventApprovedAtController;
use App\Http\Controllers\Event\UpdateEventController;
use App\Http\Controllers\Event\ViewEventListingController;
use App\Http\Controllers\Gift\GiftSuccessController;
use App\Http\Controllers\Gift\HandleSendGiftController;
use App\Http\Controllers\Gift\SendGiftController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\SupportRequestController;
use App\Http\Controllers\User\ConfirmEmailController;
use App\Http\Controllers\User\DestroyUserImageController;
use App\Http\Controllers\User\HostProfileController;
use App\Http\Controllers\User\MyConnectionsController;
use App\Http\Controllers\User\MyEventsController;
use App\Http\Controllers\User\MyProfileController;
use App\Http\Controllers\User\ResendConfirmationEmail;
use App\Http\Controllers\User\UpdateUserProfileController;
use App\Http\Controllers\User\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', HomeController::class)->name('home');
Route::post('support', SupportRequestController::class)->name('support');

Route::get('confirmemail/{event}', ConfirmEmailController::class)->name('confirm.email');
Route::post('resend-confirmation-email/{event}', ResendConfirmationEmail::class)
    ->middleware('throttle:email')
    ->name('resend-confirmation-email');
Route::get('verify/{token}', VerifyEmailController::class)->name('verify.email');

Route::middleware(['auth'])->group(function () {
    Route::get('host', HostProfileController::class)->name('host');
    Route::get('host/event', HostEventController::class)->name('host.event');
    Route::post('host/store', CreateEventController::class)->name('host.store');
    Route::put('user/profile', UpdateUserProfileController::class)->name('user.profile');

    Route::get('gift/{event}/success', GiftSuccessController::class)->name('gift.success');
    Route::get('gift/{event}', SendGiftController::class)->name('gift');
    Route::post('gift/{event}', HandleSendGiftController::class)->name('gift.store');

    Route::get('events', MyEventsController::class)->name('my.events');
    Route::delete('events/{event}', DeleteEventController::class)->name('delete.event');
    Route::put('events/{event}', UpdateEventController::class)->name('update.event');
    Route::get('events/{event}', EditEventController::class)->name('my.events.edit');
    Route::put('approved/{event}', ToggleEventApprovedAtController::class)->name('toggle-event-approved-at');
    Route::get('connections', MyConnectionsController::class)->name('my.connections');
    Route::get('profile', MyProfileController::class)->name('my.profile');
    Route::delete('destroy-photo', DestroyUserImageController::class)->name('user.profile-photo.destroy');
    Route::get('resources', ResourcesController::class)->name('resources');
    Route::put('toggle-contacted/{attendee}', ToggleConnectionContactedController::class)->name('toggle.contacted');
});

Route::get('attend/{event}', ViewEventListingController::class)->name('view.event');
Route::get('connect/{event}', [ConnectWithHostController::class, 'index'])->name('connect.index');
Route::post('connect/{event}', [ConnectWithHostController::class, 'store'])->name('connect.store');

Route::get('onetime/{token}', OneTimeLoginController::class)->name('onetime.login');
Route::get('successful', SuccessAuth0CallbackController::class)->name('successful-auth0-callback');

// for manual purposes only
Route::get('drupal-events', function () {
    return collect(Http::get('https://wildatheart.org/json/allies')->json());
});

Route::get('drupal-events/past', function () {
    $drupalEvents = collect(Http::get('https://wildatheart.org/json/allies')->json());

    return $drupalEvents->filter(function ($experience) {
        return $experience['start_date'] < now()->format('Y-m-d');
    });
});

Route::get('drupal-events/upcoming', function () {
    $drupalEvents = collect(Http::get('https://wildatheart.org/json/allies')->json());

    return $drupalEvents->filter(function ($experience) {
        return $experience['start_date'] > now()->format('Y-m-d');
    });
});

Route::get('drupal-events/null', function () {
    $drupalEvents = collect(Http::get('https://wildatheart.org/json/allies')->json());

    return $drupalEvents->filter(function ($experience) {
        return $experience['first_name'] === null || $experience['last_name'] === null;
    });

});
