<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HasOrganizationAccess;
use App\Http\Middleware\HasPermission;
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

    Route::resource('user', UserController::class)->only(['index', 'store', 'destroy', 'update', 'show'])->middleware(HasPermission::class . ':user_administration');

    Route::resource('organization', OrganizationController::class)->only(['index', 'store', 'destroy']);
    Route::resource('organization', OrganizationController::class)->only(['show', 'update']);
    Route::resource('absence', AbsenceController::class)->only(['update', 'store']);
    Route::resource('absenceType', AbsenceTypeController::class)->only(['store', 'update', 'destroy']);
    Route::resource('specialWorkingHoursFactor', SpecialWorkingHoursFactorController::class)->only(['store', 'update', 'destroy']);
    Route::resource('group', GroupController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('workLogPatch', WorkLogPatchController::class)->only(['destroy', 'store', 'update']);
    Route::resource('workLog', WorkLogController::class)->only(['index', 'store']);
    Route::get('/user/{user}/workLogs', [WorkLogController::class, 'userWorkLogs'])->name('user.workLog.index');
    Route::resource('travelLog', TravelLogController::class)->only(['store', 'update']);

    Route::resource('operatingSite', OperatingSiteController::class)->only(['index', 'store', 'destroy', 'update', 'show']);
    Route::resource('operatingTime', OperatingTimeController::class)->only(['store', 'update', 'destroy']);


    Route::singleton('profile', ProfileController::class)->only(['edit', 'update', 'destroy'])->destroyable();
});


require __DIR__ . '/auth.php';
