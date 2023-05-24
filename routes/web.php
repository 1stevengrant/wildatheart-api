<?php

use App\Http\Livewire\Home;
use App\Http\Livewire\HostCampfire;
use App\Http\Livewire\ListCampFires;
use App\Http\Livewire\ListCity;
use App\Http\Livewire\ListState;
use App\Http\Livewire\MyCampfires;
use App\Http\Livewire\ViewCampfire;
use GeoIp2\Database\Reader;
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

Route::middleware(['auth', 'role:host'])->group(function () {
    Route::get('my', MyCampfires::class)->name('my.campfires');
});

require __DIR__.'/auth.php';

Route::get('salesforce/authenticate', function () {
    return Forrest::authenticate();
});

Route::get('oauth/callback', function () {
    Forrest::callback();

    return Redirect::to('/');
});

Route::get('faqs', function () {
    return 'faqs';
})->name('faqs');

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

Route::get('find', function () {

    \App\Actions\Salesforce\ContactGetByEmail::run('steven@servant.io');

});

Route::get('account', function () {
    $query = Forrest::query('SELECT Id FROM Account');
    dd($query);
});

Route::get('ip', function () {
    ray($_SERVER['REMOTE_ADDR']);
    $cityDbReader = new Reader(resource_path('location/GeoLite2-City.mmdb'));
    $record = $cityDbReader->city(config('app.env') === 'local' ? '81.104.2.147' : request()->ip());
    ray($record);
});
