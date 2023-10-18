<?php

use App\Http\Controllers\Campfire\CreateCampfireController;
use App\Http\Controllers\Campfire\DeleteCampfireController;
use App\Http\Controllers\Campfire\UpdateCampfireController;
use App\Http\Controllers\ConfirmEmailController;
use App\Http\Controllers\ConnectWithHostController;
use App\Http\Controllers\DestroyUserImageController;
use App\Http\Controllers\GiftSuccessController;
use App\Http\Controllers\HandleSendGiftController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HostCampfireController;
use App\Http\Controllers\HostProfileController;
use App\Http\Controllers\OneTimeLoginController;
use App\Http\Controllers\Profile\EditFireController;
use App\Http\Controllers\Profile\MyConnectionsController;
use App\Http\Controllers\Profile\MyFiresController;
use App\Http\Controllers\Profile\MyProfileController;
use App\Http\Controllers\ResendConfirmationEmail;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\SendGiftController;
use App\Http\Controllers\SuccessAuth0CallbackController;
use App\Http\Controllers\SupportRequestController;
use App\Http\Controllers\ToggleConnectionContactedController;
use App\Http\Controllers\User\UpdateUserProfileController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\ViewFireListingController;
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

Route::get('/', HomeController::class)->name('home');
Route::post('support', SupportRequestController::class)->name('support');

Route::get('confirmemail/{campfire}', ConfirmEmailController::class)->name('confirm.email');
Route::post('resend-confirmation-email/{campfire}', ResendConfirmationEmail::class)
    ->middleware('throttle:email')
    ->name('resend-confirmation-email');
Route::get('verify/{token}', VerifyEmailController::class)->name('verify.email');

Route::middleware(['auth'])->group(function () {
    Route::get('host', HostProfileController::class)->name('host');
    Route::get('host/fire', HostCampfireController::class)->name('host.fire');
    Route::post('host/store', CreateCampfireController::class)->name('host.store');
    Route::put('user/profile', UpdateUserProfileController::class)->name('user.profile');

    Route::get('gift/{campfire}/success', GiftSuccessController::class)->name('gift.success');
    Route::get('gift/{campfire}', SendGiftController::class)->name('gift');
    Route::post('gift/{campfire}', HandleSendGiftController::class)->name('gift.store');

    Route::get('fires', MyFiresController::class)->name('my.campfires');
    Route::delete('fires/{campfire}', DeleteCampfireController::class)->name('delete.fire');
    Route::put('fires/{campfire}', UpdateCampfireController::class)->name('update.fire');
    Route::get('fires/{campfire}', EditFireController::class)->name('my.campfires.edit');
    Route::get('connections', MyConnectionsController::class)->name('my.connections');
    Route::get('profile', MyProfileController::class)->name('my.profile');
    Route::delete('destroy-photo', DestroyUserImageController::class)->name('user.profile-photo.destroy');
    Route::get('resources', ResourcesController::class)->name('resources');
    Route::put('toggle-contacted/{attendee}', ToggleConnectionContactedController::class)->name('toggle.contacted');
});


Route::get('attend/{campfire}', ViewFireListingController::class)->name('view.fire');
Route::get('connect/{campfire}', [ConnectWithHostController::class, 'index'])->name('connect.index');
Route::post('connect/{campfire}', [ConnectWithHostController::class, 'store'])->name('connect.store');

Route::get('onetime/{token}', OneTimeLoginController::class)->name('onetime.login');
Route::get('successful', SuccessAuth0CallbackController::class)->name('successful-auth0-callback');
