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
use Auth0\Laravel\Http\Controller\Stateful\Callback;
use Auth0\Laravel\Http\Controller\Stateful\Login;
use Auth0\Laravel\Http\Controller\Stateful\Logout;
use Illuminate\Support\Facades\Route;

Route::get('login', Login::class)->name('login');
Route::get('callback', Callback::class)->name('auth0.callback');
Route::get('logout', Logout::class)->name('logout');

Route::get('/', Home::class)->name('home')->middleware(['auth0.authenticate.optional']);
Route::get('create', CreateFire::class)->name('create')->middleware(['auth0.authenticate']);
Route::get('attend', AttendFire::class)->name('attend');
