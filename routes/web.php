<?php

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

use App\Http\Livewire\AttendFire;
use App\Http\Livewire\CreateFire;
use App\Http\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('create', CreateFire::class)->name('create');
Route::get('attend', AttendFire::class)->name('attend');

Route::get('login', function () {
    return 'login';
})->name('login');
