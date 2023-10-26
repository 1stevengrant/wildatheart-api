<?php

use App\Http\Controllers\Attendee\ConnectWithHostController;
use App\Http\Controllers\Attendee\ToggleConnectionContactedController;
use App\Http\Controllers\Auth\OneTimeLoginController;
use App\Http\Controllers\Auth\SuccessAuth0CallbackController;
use App\Http\Controllers\Campfire\CreateCampfireController;
use App\Http\Controllers\Campfire\DeleteCampfireController;
use App\Http\Controllers\Campfire\EditFireController;
use App\Http\Controllers\Campfire\HostCampfireController;
use App\Http\Controllers\Campfire\ToggleCampfireApprovedAtController;
use App\Http\Controllers\Campfire\UpdateCampfireController;
use App\Http\Controllers\Campfire\ViewFireListingController;
use App\Http\Controllers\Gift\GiftSuccessController;
use App\Http\Controllers\Gift\HandleSendGiftController;
use App\Http\Controllers\Gift\SendGiftController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\SupportRequestController;
use App\Http\Controllers\User\ConfirmEmailController;
use App\Http\Controllers\User\DestroyUserImageController;
use App\Http\Controllers\User\HostProfileController;
use App\Http\Controllers\User\MyConnectionsController;
use App\Http\Controllers\User\MyFiresController;
use App\Http\Controllers\User\MyProfileController;
use App\Http\Controllers\User\ResendConfirmationEmail;
use App\Http\Controllers\User\UpdateUserProfileController;
use App\Http\Controllers\User\VerifyEmailController;
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
    Route::put('approved/{campfire}', ToggleCampfireApprovedAtController::class)->name('toggle-campfire-approved-at');
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
