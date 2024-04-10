<?php

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

Route::prefix('v1')->group(function () {
    Route::name('v1.')->group(function () {
        Route::get('stats', \App\Http\Controllers\API\V1\StatsController::class)->name('stats');
    });
});
Route::get('events/search', \App\Http\Controllers\API\SearchForEventsController::class);
Route::get('events/types', function () {
    return response()->json(\App\Models\EventType::all());
});
