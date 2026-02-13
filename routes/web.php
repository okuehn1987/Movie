<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ChirpController;
use App\Http\Middleware\EnsureAdminIsValid;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('chirps', ChirpController::class)
        ->only(['index', 'store']);

    Route::resource('movies', MovieController::class)->only(['index', 'show']);

    Route::resource('movie', MovieController::class)->only(['store', 'create'])->middleware(EnsureAdminIsValid::class);

    Route::resource('music', MusicController::class)->only(['index', 'store']);
    Route::get('/movie/{movieFile}/getMovieContent', [MovieController::class, 'getMovieContent'])->name('movieFile.getMovieContent');

    Route::get('/movie/{thumbnailFile}/getThumbnailContent', [MovieController::class, 'getThumbnailContent'])->name('thumbnailFile.getThumbnailContent');
});
require __DIR__ . '/auth.php';
