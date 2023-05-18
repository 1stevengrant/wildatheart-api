<?php

use App\Http\Livewire\Home;
use App\Http\Livewire\HostCampfire;
use App\Http\Livewire\ListCampFires;
use App\Http\Livewire\ListState;
use App\Http\Livewire\MyCampfires;
use App\Http\Livewire\ViewCampfire;
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

Route::get('/', Home::class)->name('home');
Route::get('host', HostCampfire::class)->name('host');
Route::get('list', ListCampfires::class)->name('list.campfires');
Route::get('list/state/{state}', ListState::class)->name('list.state');
Route::get('attend/{campfire}', ViewCampfire::class)->name('view.campfire');

Route::middleware(['auth', 'role:host'])->group(function () {
    Route::get('my', MyCampfires::class)->name('my.campfires');
});

require __DIR__.'/auth.php';
