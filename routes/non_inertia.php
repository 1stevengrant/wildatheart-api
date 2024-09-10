<?php

use App\Http\Controllers\Attendee\ExportContacts;
use App\Http\Controllers\Auth\CallbackController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

Route::get('login', LoginController::class)->name('login')->middleware('guest');
Route::get('callback', CallbackController::class)->name('callback');
Route::get('logout', LogoutController::class)->name('logout')->middleware('auth');
Route::get('contacts/export/{event?}', ExportContacts::class)->name('export.contacts');
