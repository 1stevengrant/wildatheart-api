<?php

use App\Http\Controllers\Attendee\ToggleConnectionContactedController;
use App\Http\Controllers\Auth\OneTimeLoginController;
use App\Http\Controllers\Auth\SuccessAuth0CallbackController;
use App\Http\Controllers\Event\DeleteEventController;
use App\Http\Controllers\Event\EditEventController;
use App\Http\Controllers\Event\HostEventTypeBeforeController;
use App\Http\Controllers\Event\HostEventTypeController;
use App\Http\Controllers\Event\HostEventTypeFaqsController;
use App\Http\Controllers\Event\HostEventTypeMailingAddressController;
use App\Http\Controllers\Event\HostEventTypeScheduleController;
use App\Http\Controllers\Event\ToggleEventApprovedAtController;
use App\Http\Controllers\Event\UpdateEventController;
use App\Http\Controllers\Gift\HandleSendGiftController;
use App\Http\Controllers\HostEventTypeCreateEventController;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\User\AgreeToHostAgreementController;
use App\Http\Controllers\User\DestroyUserImageController;
use App\Http\Controllers\User\HostAgreementPageController;
use App\Http\Controllers\User\HostProfileController;
use App\Http\Controllers\User\MyConnectionsController;
use App\Http\Controllers\User\MyEventsController;
use App\Http\Controllers\User\MyProfileController;
use App\Http\Controllers\User\UpdateUserProfileController;
use App\Models\EventType;

Route::get('onetime/{token}', OneTimeLoginController::class)->name('onetime.login');
Route::get('successful', SuccessAuth0CallbackController::class)->name('successful-auth0-callback');

Route::middleware(['auth'])->group(function () {
    Route::get('host/agreement', HostAgreementPageController::class)->name('host.agreement');
    Route::post('host/agreement', AgreeToHostAgreementController::class)->name('host.agreement.store');

    Route::put('user/profile', UpdateUserProfileController::class)->name('user.profile');

    Route::get('host/select', function () {
        return inertia('HostEventTypeSelectPage', [
            'experienceTypes' => EventType::query()->slug('%experience%')->active()->get(),
            'retreatTypes' => EventType::query()
                ->slug('%retreat%')
                ->slugOr('%school%')
                ->active()
                ->get(),
            'fireTypes' => EventType::query()->slug('%fire%')->active()->get(),
        ]);
    })->name('host.eventType.select');

    Route::middleware(['agreed_to_host_agreement'])->group(function () {
        Route::get('host', HostProfileController::class)->name('host');

        Route::name('host.eventType.')->group(function () {
            Route::prefix('host/{eventType}')->group(function () {
                Route::get('/', HostEventTypeController::class)->name('index');
                Route::get('before', HostEventTypeBeforeController::class)->name('before');
                Route::get('schedule', HostEventTypeScheduleController::class)->name('schedule');
                Route::get('faqs', HostEventTypeFaqsController::class)->name('faqs');
                Route::get('create', [HostEventTypeCreateEventController::class, 'index'])->name('create');
                Route::post('create', [HostEventTypeCreateEventController::class, 'store'])->name('create.store');
                Route::get('mailing', [HostEventTypeMailingAddressController::class, 'index'])->name('mailing');
                Route::post('mailing', [HostEventTypeMailingAddressController::class, 'store'])->name('mailing.store');
            });
        });

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
});
