<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ChirpController;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('chirps', ChirpController::class)
    ->only(['index', 'store'])
    ->middleware(['auth', 'verified']);

Route::resource('movies', MoviesController::class)
    ->only(['index', 'store', 'create'])
    ->middleware(['auth', 'verified']);

Route::resource('music', MusicController::class)
    ->only(['index', 'store'])
    ->middleware('auth', 'verified');
require __DIR__ . '/auth.php';
