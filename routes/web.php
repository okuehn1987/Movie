<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HasOrganizationAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect(route('dashboard'));
    }
    return redirect('login');
})->name('home');


Route::middleware(['auth', HasOrganizationAccess::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('user', UserController::class)->only(['index', 'store', 'destroy', 'update', 'show']);
    Route::singleton('profile', ProfileController::class)->only(['edit', 'update', 'destroy'])->destroyable();
    Route::resource('organization', OrganizationController::class)->only(['index', 'store', 'destroy']);
    Route::resource('organization', OrganizationController::class)->only(['show', 'update']);
    Route::resource('absence', AbsenceController::class)->only(['update', 'store']);
    Route::resource('absenceType', AbsenceTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('specialWorkingHoursFactor', SpecialWorkingHoursFactorController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('group', GroupController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('workLog', WorkLogController::class)->only(['index', 'store', 'update']);
    Route::resource('travelLog', TravelLogController::class)->only(['store', 'update']);
    Route::resource('operatingSite', OperatingSiteController::class)->only(['index', 'store', 'destroy', 'update', 'show']);
    Route::resource('operatingTime', OperatingTimeController::class)->only(['store', 'update', 'destroy']);
});


require __DIR__ . '/auth.php';
