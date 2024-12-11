<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\VerifyEmailController;
use App\Http\Controllers\User\ConfirmEmailController;
use App\Http\Controllers\User\ResendConfirmationEmail;

Route::get('confirmemail/{event}', ConfirmEmailController::class)->name('confirm.email');
Route::post('resend-confirmation-email/{event}', ResendConfirmationEmail::class)
    ->middleware('throttle:email')
    ->name('resend-confirmation-email');
Route::get('verify/{token}', VerifyEmailController::class)->name('verify.email');
