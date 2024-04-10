<?php

use App\Http\Controllers\Attendee\ConnectWithHostController;
use App\Http\Controllers\Event\ViewEventListingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SupportRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::post('support', SupportRequestController::class)->name('support');

include 'auth.php';
include 'emails.php';

Route::get('attend/{event}', ViewEventListingController::class)->name('view.event');
Route::get('connect/{event}', [ConnectWithHostController::class, 'index'])->name('connect.index');
Route::post('connect/{event}', [ConnectWithHostController::class, 'store'])->name('connect.store');

include 'drupal.php';
