<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
require __DIR__ . '/auth.php';
