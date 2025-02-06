<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AllEventsController;
use App\Http\Controllers\API\BlogsController;
use App\Http\Controllers\API\V1\StatsController;
use App\Http\Controllers\API\SubsplashController;
use App\Http\Controllers\API\MediaItemDataController;
use App\Http\Controllers\API\ExperienceDataController;
use App\Http\Controllers\API\MediaSeriesDataController;
use App\Http\Controllers\API\SearchForEventsController;
use App\Http\Controllers\API\V1\Content\PageEntryController;
use App\Http\Controllers\API\V1\Content\AlertEntryController;
use App\Http\Controllers\API\V1\Content\DailyReadingController;
use App\Http\Controllers\API\V1\Content\PrayersEntryController;
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
});

Route::prefix('content')->group(function () {
    Route::name('content.')->group(function () {
        Route::get('/blogs', [BlogsController::class, 'index'])->name('blogs.index');
        Route::get('/blogs/{id}', [BlogsController::class, 'show'])->name('blogs.show');
        Route::get('/series/{slug}', [MediaSeriesDataController::class, 'show'])->name('series.show');

        Route::get('/media-by-series-id/{slug}', [MediaItemDataController::class, 'mediaBySeriesId'])->name('media.bySeriesId');
        Route::get('/media/redirects', [MediaItemDataController::class, 'getRedirects'])->name('media.getRedirects');
        Route::get('/media/{slug}', [MediaItemDataController::class, 'media'])->name('media');
        Route::get('/subsplash/{any}', [SubsplashController::class, 'getProxy'])->name('subsplash.getProxy')->where('any', '.*');
        Route::get('/subsplash-media/{slug}', [MediaItemDataController::class, 'subsplashMedia'])->name('media.show');
        Route::get('/experiences/all', [ExperienceDataController::class, 'all'])->name('experience.all');
        Route::get('/experiences/{slug}', [ExperienceDataController::class, 'show'])->name('experience.show');
        Route::prefix('v1')->name('v1.')->group(function () {
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
            Route::get('/alerts', [AlertEntryController::class, 'index'])->name('alerts.index');
        });
    });
});

Route::get('events/search', SearchForEventsController::class)->name('api.events.search');
Route::get('events/all', AllEventsController::class)->name('api.events.all');
