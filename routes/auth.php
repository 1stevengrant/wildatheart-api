<?php

use App\Http\Controllers\Attendee\ToggleConnectionContactedController;
use App\Http\Controllers\Auth\OneTimeLoginController;
use App\Http\Controllers\Auth\SuccessAuth0CallbackController;
use App\Http\Controllers\Event\CreateEventController;
use App\Http\Controllers\Event\DeleteEventController;
use App\Http\Controllers\Event\EditEventController;
use App\Http\Controllers\Event\HostEventController;
use App\Http\Controllers\Event\ToggleEventApprovedAtController;
use App\Http\Controllers\Event\UpdateEventController;
use App\Http\Controllers\Gift\GiftSuccessController;
use App\Http\Controllers\Gift\HandleSendGiftController;
use App\Http\Controllers\Gift\SendGiftController;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\User\DestroyUserImageController;
use App\Http\Controllers\User\HostProfileController;
use App\Http\Controllers\User\MyConnectionsController;
use App\Http\Controllers\User\MyEventsController;
use App\Http\Controllers\User\MyProfileController;
use App\Http\Controllers\User\UpdateUserProfileController;

Route::get('onetime/{token}', OneTimeLoginController::class)->name('onetime.login');
Route::get('successful', SuccessAuth0CallbackController::class)->name('successful-auth0-callback');

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
