<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\AllEventsController;
use App\Http\Controllers\API\BlogsController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\V1\EventController;
use App\Http\Controllers\API\V1\StatsController;
use App\Http\Controllers\API\SubsplashController;
use App\Http\Controllers\API\V1\EventTypeController;
use App\Http\Controllers\API\MediaItemDataController;
use App\Http\Controllers\API\ExperienceDataController;
use App\Http\Controllers\API\MediaSeriesDataController;
use App\Http\Controllers\API\SearchForEventsController;
use App\Http\Controllers\API\V1\Navs\AllNavsController;
use App\Http\Controllers\API\V1\Navs\MobileNavController;
use App\Http\Controllers\API\V1\Navs\DesktopNavController;
use App\Http\Controllers\API\V1\Content\PageEntryController;
use App\Http\Controllers\API\V1\Content\AlertEntryController;
use App\Http\Controllers\API\V1\Content\EventsEntryController;
use App\Http\Controllers\API\V1\Content\DailyReadingController;
use App\Http\Controllers\API\V1\Content\PrayersEntryController;
use App\Http\Controllers\API\V1\Content\SubsplashAppController;
use App\Http\Controllers\API\V1\Content\MediaItemEntryController;
use App\Http\Controllers\API\V1\Content\MediaItemOrSeriesController;

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

    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::post('refresh-daily-readings', [DailyReadingController::class, 'refreshDailyReadings'])->name('refreshDailyReadings');
    });

    Route::prefix('navs')->name('navs.')->group(function () {
        Route::get('all', AllNavsController::class)->name('all');
        Route::get('desktop', DesktopNavController::class)->name('desktop');
        Route::get('mobile', MobileNavController::class)->name('mobile');
    });

    Route::prefix('events')->name('events.')->group(function () {
        Route::get('', [EventController::class, 'index'])->name('events.index');
        Route::get('search', \App\Http\Controllers\API\V1\SearchForEventsController::class)->name('api.events.search');
        Route::get('types', EventTypeController::class)->name('types');
        Route::get('{event}', [EventController::class, 'show'])->name('events.show');
    });

});

Route::prefix('content')->group(function () {
    Route::name('content.')->group(function () {
        Route::get('/series/{slug}', [MediaSeriesDataController::class, 'show'])->name('series.show');

        Route::get('/media-by-series-id/{slug}', [MediaItemDataController::class, 'mediaBySeriesId'])->name('media.bySeriesId');
        Route::get('/media/redirects', [MediaItemDataController::class, 'getRedirects'])->name('media.getRedirects');
        Route::get('/media/{slug}', [MediaItemDataController::class, 'media'])->name('media');
        Route::get('/subsplash/{any}', [SubsplashController::class, 'getProxy'])->name('subsplash.getProxy')->where('any', '.*');
        Route::get('/subsplash-media/{slug}', [MediaItemDataController::class, 'subsplashMedia'])->name('media.show');
        Route::get('/experiences/all', [ExperienceDataController::class, 'all'])->name('experience.all');
        Route::get('/experiences/{slug}', [ExperienceDataController::class, 'show'])->name('experience.show');
        Route::prefix('v1')->name('v1.')->group(function () {
            Route::get('/seo/sitemap', [SeoController::class, 'getSitemap'])->name('seo.sitemap');
            Route::prefix('search')->name('search.')->group(function () {
                Route::get('/', [SearchController::class, 'index'])->name('index');
            });
            Route::prefix('blogs')->name('blogs.')->group(function () {
                Route::get('/', [BlogsController::class, 'index'])->name('index');
                Route::get('/{uri}', [BlogsController::class, 'show'])->name('show')->where('uri', '.*');
            });
            Route::prefix('daily-reading')->name('dailyReading.')->group(function () {
                Route::get('/', [DailyReadingController::class, 'index'])->name('index');
                Route::get('/recent', [DailyReadingController::class, 'getRecent'])->name('getRecent');
                Route::get('/redirect/{slug}', [DailyReadingController::class, 'redirect'])->name('redirect');
            });
            Route::get('/page/{slug}', [PageEntryController::class, 'getPageBySlug'])->name('pages.bySlug');
            Route::get('/media/{slug}', [MediaItemEntryController::class, 'media'])->name('media');
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
                Route::get('/daily-readings', [SubsplashAppController::class, 'dailyReadingToday'])->name('dailyReadings');
                Route::get('/daily-readings/listen/{id?}', [SubsplashAppController::class, 'dailyReadingsListen'])->name('dailyReadingsListen');
                Route::get('/daily-readings/list', [SubsplashAppController::class, 'dailyReadingsList'])->name('dailyReadingsList');
                Route::get('/daily-readings/{id}', [SubsplashAppController::class, 'dailyReading'])->name('dailyReadingById');
                Route::get('/prayers', [SubsplashAppController::class, 'prayers'])->name('prayers');
                Route::get('/prayers/{id}', [SubsplashAppController::class, 'prayer'])->name('prayer');
                Route::get('/prayers/listen/{id}', [SubsplashAppController::class, 'listenPrayer'])->name('listenPrayer');
            });
        });
    });
});

Route::get('events/search', SearchForEventsController::class)->name('api.events.search');
Route::get('events/all', AllEventsController::class)->name('api.events.all');
