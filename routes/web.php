<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ChirpController;
use App\Http\Middleware\EnsureAdminIsValid;
use App\Http\Middleware\SetCacheControl;
use GuzzleHttp\Promise\Create;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('chirps', ChirpController::class)
        ->only(['index', 'store']);

    Route::resource('movies', MovieController::class)->only(['index', 'show']);

    Route::resource('movie', MovieController::class)->only(['store', 'create', 'update'])->middleware(EnsureAdminIsValid::class);

    Route::resource('actor', ActorController::class)->only(['index', 'store', 'create', 'update', 'delete'])->middleware(EnsureAdminIsValid::class);

    Route::resource('music', MusicController::class)->only(['index', 'store']);
    Route::get('/movie/{movie}/getMovieContent', [MovieController::class, 'getMovieContent'])->name('movie.getMovieContent');

    Route::get('/movie/{movie}/getThumbnailContent', [MovieController::class, 'getThumbnailContent'])->name('movie.getThumbnailContent');
    Route::resource('movie.comment', CommentController::class)->only(['store', 'update'])->shallow();
});
require __DIR__ . '/auth.php';
