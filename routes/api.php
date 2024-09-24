<?php

use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\ExperienceDataController;
use App\Http\Controllers\API\MediaItemDataController;
use App\Http\Controllers\API\MediaSeriesDataController;
use App\Http\Controllers\API\SubsplashController;
use App\Http\Controllers\API\V1\Content\MediaItemEntryController;
use App\Http\Controllers\API\V1\Content\PageEntryController;
use App\Http\Controllers\API\V1\StatsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('events', EventController::class);
Route::get('users', UserController::class);

Route::prefix('v1')->group(function () {
    Route::name('v1.')->group(function () {
        Route::get('stats', StatsController::class)->name('stats');
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
            Route::get('/page/{slug}', [PageEntryController::class, 'getPageBySlug'])->name('pages.bySlug');
            Route::get('/media/{slug}', [MediaItemEntryController::class, 'media'])->name('media');
        });
    });
});

Route::get('events/search', \App\Http\Controllers\API\SearchForEventsController::class);
Route::get('events', function () {
    return \App\Http\Resources\API\EventAPIResource::collection(\App\Models\Event::paginate(200));
});
