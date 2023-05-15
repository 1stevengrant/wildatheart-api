<?php

use App\Http\Livewire\AttendFire;
use App\Http\Livewire\Home;
use App\Http\Livewire\HostFire;
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
Route::get('host', HostFire::class)->name('host');
Route::get('attend', AttendFire::class)->name('attend');

require __DIR__.'/auth.php';
