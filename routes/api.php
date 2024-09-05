<?php

use App\Http\Controllers\API\EventController;
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
