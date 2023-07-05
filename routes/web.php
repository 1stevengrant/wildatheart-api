<?php

use App\Http\Livewire\ConfirmEmail;
use App\Http\Livewire\ConnectToHost;
use App\Http\Livewire\Home;
use App\Http\Livewire\HostCampfire;
use App\Http\Livewire\ListCampfires;
use App\Http\Livewire\ListCity;
use App\Http\Livewire\ListState;
use App\Http\Livewire\MyCampfires;
use App\Http\Livewire\SendGift;
use App\Http\Livewire\UpdateAddress;
use App\Http\Livewire\VerifyEmail;
use App\Http\Livewire\ViewCampfire;
use App\Mail\Campfire\CampfireApproved;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;

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
Route::get('list/city/{city}', ListCity::class)->name('list.city');
Route::get('attend/{campfire}', ViewCampfire::class)->name('view.campfire');
Route::get('connect/{campfire}', ConnectToHost::class)->name('connect.host');
Route::get('confirmemail/{campfire}', ConfirmEmail::class)->name('confirm.email');
Route::get('verify/{token}', VerifyEmail::class)->name('verify.email');
Route::get('gift/{campfire}', SendGift::class)->name('gift');

Route::middleware(['auth', 'role:host'])->group(function () {
    Route::get('my', MyCampfires::class)->name('my.campfires');
});

Route::get('update-address/{campfire}', UpdateAddress::class)->name('update.address');

require __DIR__.'/auth.php';

Route::get('salesforce/authenticate', function () {
    return Forrest::authenticate();
});

Route::get('oauth/callback', function () {
    Forrest::callback();

    return Redirect::to('/');
});

Route::middleware(['role:admin'])->group(function () {
    Route::get('mailable', function () {
        $campfire = App\Models\Campfire::first();

        return new CampfireApproved($campfire);
    });

    Route::get('account', function () {
        $query = Forrest::query('SELECT Id FROM Account');
        dd($query);
    });

    Route::get('test', function () {
        $response = Forrest::sobjects('Contact', [
            'method' => 'post',
            'body' => [
                'FirstName' => 'Steven',
                'LastName' => 'Steven',
                'Email' => 'steven@servant.io',
                'Phone' => '+447766494949',
            ],
        ]);
        dd($response);
    });
});
