<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('login');
});

Route::middleware('auth')->group(function () {
    Route::inertia('/dashboard', "Welcome", ['users' => User::inOrganization()->select('id')->get()])->name("dashboard");
    Route::singleton('profile', ProfileController::class)->only(['edit', 'update', 'destroy'])->destroyable();
    Route::resource('organization', OrganizationController::class)->only(['index', 'store', 'destroy']);
    Route::resource('organization', OrganizationController::class)->only(['show', 'update']);
    Route::resource('abscence', AbscenceController::class)->only(['update', 'store']);
    Route::resource('abscenceType', AbscenceTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('specialWorkingHoursFactor', SpecialWorkingHoursFactorController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('group', GroupController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('user', UserController::class)->only(['index', 'store', 'destroy', 'update', 'show']);
    Route::resource('workLog', WorkLogController::class)->only(['index', 'store', 'update']);
    Route::resource('travelLog', TravelLogController::class)->only(['store', 'update']);
    Route::resource('operatingSite', OperatingSiteController::class)->only(['index', 'store', 'destroy', 'update', 'show']);
    Route::resource('operatingTime', OperatingTimeController::class)->only(['store', 'update', 'destroy']);
});


require __DIR__ . '/auth.php';
