<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\EventGuideController;
use App\Http\Controllers\Gift\SendGiftController;
use App\Http\Controllers\User\MyProfileController;
use App\Http\Controllers\Event\EditEventController;
use App\Http\Controllers\User\MyContactsController;
use App\Http\Controllers\User\HostProfileController;
use App\Http\Controllers\Event\DeleteEventController;
use App\Http\Controllers\Event\UpdateEventController;
use App\Http\Controllers\Event\EventManagerController;
use App\Http\Controllers\Event\StandardFireController;
use App\Http\Controllers\Event\HostEventTypeController;
use App\Http\Controllers\User\MyActiveEventsController;
use App\Http\Controllers\Event\CaptivatingFireController;
use App\Http\Controllers\User\DestroyUserImageController;
use App\Http\Controllers\User\MyInactiveEventsController;
use App\Http\Controllers\User\HostAgreementPageController;
use App\Http\Controllers\User\UpdateUserProfileController;
use App\Http\Controllers\Event\HostEventTypeFaqsController;
use App\Http\Controllers\Event\ToggleEventPrivacyController;
use App\Http\Controllers\Event\HostEventTypeBeforeController;
use App\Http\Controllers\Event\HostEventTypeScheduleController;
use App\Http\Controllers\Event\ToggleEventApprovedAtController;
use App\Http\Controllers\Event\EventOwnershipTransferController;
use App\Http\Controllers\Event\HostEventTypeCreateEventController;
use App\Http\Controllers\Attendee\ToggleContactContactedController;
use App\Http\Controllers\Event\HostEventTypeMailingAddressController;

Route::middleware(['auth'])->group(function () {

    Route::put('user/profile', UpdateUserProfileController::class)->name('user.profile');
    Route::get('profile', MyProfileController::class)->name('my.profile');
    Route::delete('destroy-photo', DestroyUserImageController::class)->name('user.profile-photo.destroy');

    Route::middleware(['profile_complete'])->group(function () {

        Route::prefix('host')->name('host.')->group(function () {
            Route::get('agreement', [HostAgreementPageController::class, 'index'])->name('agreement');
            Route::post('agreement', [HostAgreementPageController::class, 'store'])->name('agreement.store');
            Route::get('select', ResourcesController::class)->name('eventType.select');
        });

        Route::middleware(['agreed_to_host_agreement'])->group(function () {

            Route::prefix('host')->name('host.')->group(function () {
                Route::get('/', HostProfileController::class)->name('index');
                Route::get('fire', [StandardFireController::class, 'index'])->name('fire');
                Route::post('fire', [StandardFireController::class, 'store'])->name('fire.store');

                Route::get('captivating-fire', [CaptivatingFireController::class, 'index'])->name('captivating-fire');
                Route::post('captivating-fire', [CaptivatingFireController::class, 'store'])->name('captivating-fire.store');
            });

            Route::prefix('gift')->name('gift.')->group(function () {
                Route::get('{event}', [SendGiftController::class, 'index'])->name('index');
                Route::post('{event}', [SendGiftController::class, 'store'])->name('store');
            });

            Route::prefix('host/{eventType}')->name('host.eventType.')->group(function () {
                Route::get('/', HostEventTypeController::class)->name('index');
                Route::get('before', HostEventTypeBeforeController::class)->name('before');
                Route::get('schedule', HostEventTypeScheduleController::class)->name('schedule');
                Route::get('faqs', HostEventTypeFaqsController::class)->name('faqs');
                Route::get('mailing', [HostEventTypeMailingAddressController::class, 'index'])->name('mailing');
                Route::post('mailing', [HostEventTypeMailingAddressController::class, 'store'])->name('mailing.store');
                Route::get('create', [HostEventTypeCreateEventController::class, 'index'])->name('create');
                Route::post('create', [HostEventTypeCreateEventController::class, 'store'])->name('create.store');
            });

            Route::prefix('events')->group(function () {
                Route::get('active', MyActiveEventsController::class)->name('my.events.active');
                Route::get('inactive', MyInactiveEventsController::class)->name('my.events.inactive');
                Route::delete('{event}', DeleteEventController::class)->name('delete.event');
                Route::put('{event}', UpdateEventController::class)->name('update.event');
                Route::get('{event}', EditEventController::class)->name('my.events.edit');

                Route::prefix('{event}/managers')->name('events.managers.')->group(function () {
                    Route::get('/', [EventManagerController::class, 'index'])->name('index');
                    Route::post('/', [EventManagerController::class, 'store'])->name('store');
                    Route::delete('{manager}', [EventManagerController::class, 'destroy'])->name('destroy');
                });

                Route::prefix('{event}/ownership-transfer')->name('events.ownership.')->group(function () {
                    Route::get('/', [EventOwnershipTransferController::class, 'show'])->name('show');
                    Route::post('/', [EventOwnershipTransferController::class, 'store'])->name('store');
                    Route::delete('/', [EventOwnershipTransferController::class, 'destroy'])->name('destroy');
                });
            });

            Route::put('approved/{event}', ToggleEventApprovedAtController::class)->name('toggle-event-approved-at');
            Route::get('contacts', [MyContactsController::class, 'index'])->name('my.contacts');
            Route::delete('contacts/{attendee}', [MyContactsController::class, 'destroy'])->name('my.contacts.destroy');
            Route::get('resources', ResourcesController::class)->name('resources');
            Route::put('toggle-contacted/{attendee}', ToggleContactContactedController::class)->name('toggle.contacted');
            Route::put('toggle-privacy/{event}', ToggleEventPrivacyController::class)->name('toggle-event-privacy');

            Route::get('{eventType}/event-guide', EventGuideController::class)->name('eventType.guide');
        });
    });
});
