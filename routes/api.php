<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\AllEventsController;
use App\Http\Controllers\API\BlogsController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\V1\EventController;
use App\Http\Controllers\API\V1\StatsController;
use App\Http\Controllers\API\SubsplashController;
use App\Http\Controllers\API\V1\AttendeeController;
use App\Http\Controllers\API\V1\EventTypeController;
use App\Http\Controllers\API\V1\HostEventController;
use App\Http\Controllers\API\AllExperiencesController;
use App\Http\Controllers\API\ExperienceDataController;
use App\Http\Controllers\API\V1\CurrentUserController;
use App\Http\Controllers\API\Media\MediaItemController;
use App\Http\Controllers\API\MediaSeriesDataController;
use App\Http\Controllers\API\SearchForEventsController;
use App\Http\Controllers\API\V1\HostContactsController;
use App\Http\Controllers\API\V1\Navs\AllNavsController;
use App\Http\Controllers\API\V1\EventInterestController;
use App\Http\Controllers\API\V1\Navs\MobileNavController;
use App\Http\Controllers\API\V1\Navs\DesktopNavController;
use App\Http\Controllers\API\V1\GroupedEventTypeController;
use App\Http\Controllers\API\Media\MediaRedirectsController;
use App\Http\Controllers\API\V1\Content\PageEntryController;
use App\Http\Controllers\API\V1\ContactInvitationsController;
use App\Http\Controllers\API\V1\Content\AlertEntryController;
use App\Http\Controllers\API\V1\HostMailingAddressController;
use App\Http\Controllers\API\V1\Content\EventsEntryController;
use App\Http\Controllers\API\V1\ArchivedHostContactsController;
use App\Http\Controllers\API\V1\Content\DailyReadingController;
use App\Http\Controllers\API\V1\Content\PrayersEntryController;
use App\Http\Controllers\API\Media\MediaItemsBySeriesController;
use App\Http\Controllers\API\Media\SubsplashMediaItemController;
use App\Http\Controllers\API\V1\Content\MediaItemEntryController;
use App\Http\Controllers\API\V1\HostContactInvitationsController;
use App\Http\Controllers\API\V1\Content\MediaItemOrSeriesController;
use App\Http\Controllers\API\V1\Content\RecentDailyReadingsController;
use App\Http\Controllers\API\V1\Content\RefreshDailyReadingsController;
use App\Http\Controllers\API\V1\Content\SubsplashApp\PrayerAudioController;
use App\Http\Controllers\API\V1\Content\SubsplashApp\DailyReadingsController;
use App\Http\Controllers\API\V1\Content\SubsplashApp\DailyReadingAudioController;
use App\Http\Controllers\API\V1\Content\SubsplashApp\TodaysDailyReadingController;
use App\Http\Controllers\API\V1\SearchForEventsController as V1SearchForEventsController;
use App\Http\Controllers\API\V1\Content\SubsplashApp\PrayersController as SubsplashPrayersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->name('v1.')->group(function () {
    Route::get('stats', StatsController::class)->name('stats');

    Route::get('me', CurrentUserController::class)
        ->middleware(['auth0.jwt', 'throttle:userProfile'])
        ->name('me');

    Route::patch('users/me', [CurrentUserController::class, 'update'])
        ->middleware(['auth0.jwt', 'throttle:userProfile'])
        ->name('users.me.update');

    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::post('refresh-daily-readings', RefreshDailyReadingsController::class)->name('refreshDailyReadings');
    });

    Route::prefix('navs')->name('navs.')->group(function () {
        Route::get('all', AllNavsController::class)->name('all');
        Route::get('desktop', DesktopNavController::class)->name('desktop');
        Route::get('mobile', MobileNavController::class)->name('mobile');
    });

    Route::prefix('host')->name('host.')->middleware('auth0.jwt')->group(function () {
        Route::get('events', [HostEventController::class, 'index'])->name('events.index');
        Route::post('mailing-address', [HostMailingAddressController::class, 'store'])->name('mailing-address.store');
        Route::post('event-types/{eventType}/events', [HostEventController::class, 'store'])->name('event-types.events.store');
        Route::post('event-types/{eventType}/events/draft', [HostEventController::class, 'storeDraft'])->name('event-types.events.draft');
        Route::post('event-types/{eventType}/events/{event}/publish', [HostEventController::class, 'publish'])->name('event-types.events.publish');
        Route::delete('events/{event}', [HostEventController::class, 'destroy'])
            ->where('event', '[0-9a-fA-F-]{36}')
            ->name('events.destroy');
        Route::post('events/{event}/unpublish', [HostEventController::class, 'unpublish'])
            ->where('event', '[0-9a-fA-F-]{36}')
            ->name('events.unpublish');
        Route::post('events/{event}/publish', [HostEventController::class, 'approve'])
            ->where('event', '[0-9a-fA-F-]{36}')
            ->name('events.approve');
        Route::get('contacts', [HostContactsController::class, 'index'])->name('contacts.index');
        Route::post('contact-invitations', [HostContactInvitationsController::class, 'store'])->name('contact-invitations.store');
        Route::delete('contact-invitations/{invitation}', [HostContactInvitationsController::class, 'destroy'])->name('contact-invitations.destroy');
        Route::post('contacts/{event}/{user}/archive', [ArchivedHostContactsController::class, 'store'])
            ->where('event', '[0-9a-fA-F-]{36}')
            ->name('contacts.archive.store');
        Route::delete('contacts/{event}/{user}/archive', [ArchivedHostContactsController::class, 'destroy'])
            ->where('event', '[0-9a-fA-F-]{36}')
            ->name('contacts.archive.destroy');
    });

    Route::prefix('contact-invitations')->name('contact-invitations.')->group(function () {
        Route::post('preview', [ContactInvitationsController::class, 'preview'])->name('preview');
        Route::post('accept', [ContactInvitationsController::class, 'accept'])->name('accept');
        Route::post('decline', [ContactInvitationsController::class, 'decline'])->name('decline');
    });

    Route::prefix('events')->name('events.')->group(function () {
        Route::get('', [EventController::class, 'index'])->name('events.index');
        Route::get('search', V1SearchForEventsController::class)->name('search');
        Route::get('types', EventTypeController::class)->name('types');
        Route::get('types/grouped', GroupedEventTypeController::class)->name('types.grouped');
        Route::get('{event}', [EventController::class, 'show'])->name('events.show');
        Route::post('{event}/connect', [AttendeeController::class, 'store'])
            ->middleware('throttle:connect')
            ->name('connect.store');

        Route::middleware(['auth0.jwt', 'throttle:interests'])->group(function () {
            Route::get('{event}/interests/me', [EventInterestController::class, 'show'])
                ->where('event', '[0-9a-fA-F-]{36}')
                ->name('interests.show');
            Route::post('{event}/interests', [EventInterestController::class, 'store'])
                ->where('event', '[0-9a-fA-F-]{36}')
                ->name('interests.store');
            Route::delete('{event}/interests/me', [EventInterestController::class, 'destroy'])
                ->where('event', '[0-9a-fA-F-]{36}')
                ->name('interests.destroy');
        });
    });
});

Route::prefix('content')->group(function () {
    Route::name('content.')->group(function () {
        Route::get('/series/{slug}', [MediaSeriesDataController::class, 'show'])->name('series.show');

        Route::get('/media-by-series-id/{slug}', [MediaItemsBySeriesController::class, 'show'])->name('media.bySeriesId');
        Route::get('/media/redirects', [MediaRedirectsController::class, 'index'])->name('media.getRedirects');
        Route::get('/media/{slug}', [MediaItemController::class, 'show'])->name('media');
        Route::get('/subsplash/{any}', SubsplashController::class)->name('subsplash.getProxy')->where('any', '.*');
        Route::get('/subsplash-media/{slug}', [SubsplashMediaItemController::class, 'show'])->name('media.show');
        Route::get('/experiences/all', AllExperiencesController::class)->name('experience.all');
        Route::get('/experiences/{slug}', [ExperienceDataController::class, 'show'])->name('experience.show');
        Route::prefix('v1')->name('v1.')->group(function () {
            Route::get('/seo/sitemap', SitemapController::class)->name('seo.sitemap');
            Route::prefix('search')->name('search.')->group(function () {
                Route::get('/', [SearchController::class, 'index'])->name('index');
            });
            Route::prefix('blogs')->name('blogs.')->group(function () {
                Route::get('/', [BlogsController::class, 'index'])->name('index');
                Route::get('/{uri}', [BlogsController::class, 'show'])->name('show')->where('uri', '.*');
            });
            Route::prefix('daily-reading')->name('dailyReading.')->group(function () {
                Route::get('/', [DailyReadingController::class, 'index'])->name('index');
                Route::get('/recent', [RecentDailyReadingsController::class, 'index'])->name('getRecent');
            });
            Route::get('/page/{slug}', [PageEntryController::class, 'show'])->name('pages.bySlug');
            Route::get('/media/{slug}', [MediaItemEntryController::class, 'show'])->name('media');
            Route::get('/media-or-series/{slugOrUri}', [MediaItemOrSeriesController::class, 'show'])->name('mediaOrSeries')->where('slugOrUri', '.*');
            Route::prefix('prayers')->name('prayers.')->group(function () {
                Route::get('/', [PrayersEntryController::class, 'index'])->name('index');
                Route::get('/{slug}', [PrayersEntryController::class, 'show'])->name('show');
            });
            Route::prefix('events')->name('events.')->group(function () {
                Route::get('/', [EventsEntryController::class, 'index'])->name('index');
                Route::get('/{slug}', [EventsEntryController::class, 'show'])->name('show');
            });
            Route::get('/alerts', [AlertEntryController::class, 'index'])->name('alerts.index');
            Route::prefix('subsplash')->name('subsplash.')->group(function () {
                Route::get('/daily-readings', TodaysDailyReadingController::class)->name('dailyReadings');
                Route::get('/daily-readings/listen/{id?}', [DailyReadingAudioController::class, 'show'])->name('dailyReadingsListen');
                Route::get('/daily-readings/list', [DailyReadingsController::class, 'index'])->name('dailyReadingsList');
                Route::get('/daily-readings/{id}', [DailyReadingsController::class, 'show'])->name('dailyReadingById');
                Route::get('/prayers', [SubsplashPrayersController::class, 'index'])->name('prayers');
                Route::get('/prayers/{id}', [SubsplashPrayersController::class, 'show'])->name('prayer');
                Route::get('/prayers/listen/{id}', [PrayerAudioController::class, 'show'])->name('listenPrayer');
            });
        });
    });
});

Route::get('events/search', SearchForEventsController::class)->name('api.events.search.legacy');
Route::get('events/all', AllEventsController::class)->name('api.events.all');
