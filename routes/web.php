<?php

namespace App\Http\Controllers;

use App\Http\Middleware\HasOrganizationAccess;
use App\Http\Middleware\HasPermission;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', HasOrganizationAccess::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('user', UserController::class)->only(['index', 'store', 'destroy', 'update', 'show'])->middleware(HasPermission::class . ':user_administration');

    Route::resource('organization', OrganizationController::class)->only(['index', 'store', 'destroy']);
    Route::resource('organization', OrganizationController::class)->only(['show', 'update']);
    Route::resource('absence', AbsenceController::class)->only(['index', 'update', 'store']);
    Route::resource('absenceType', AbsenceTypeController::class)->only(['store', 'update', 'destroy']);
    Route::resource('specialWorkingHoursFactor', SpecialWorkingHoursFactorController::class)->only(['store', 'update', 'destroy']);
    Route::resource('group', GroupController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('workLogPatch', WorkLogPatchController::class)->only(['destroy', 'store', 'update']);
    Route::resource('workLog', WorkLogController::class)->only(['index', 'store']); // FIXME: is index still used?
    Route::get('/user/{user}/workLogs', [WorkLogController::class, 'userWorkLogs'])->name('user.workLog.index');
    Route::get('/users/workLogs', [WorkLogController::class, 'workLogs'])->name('users.workLogs'); // FIXME: move to workLog.index if possible
    Route::resource('travelLog', TravelLogController::class)->only(['store', 'update']);

    Route::resource('operatingSite', OperatingSiteController::class)->only(['index', 'store', 'destroy', 'update', 'show']);
    Route::resource('operatingTime', OperatingTimeController::class)->only(['store', 'update', 'destroy']);

    Route::resource('user.timeAccount', TimeAccountController::class)->shallow()->only(['store']);
    Route::resource('timeAccountTransaction', TimeAccountTransactionController::class)->only(['store']);
    Route::resource('timeAccountSetting', TimeAccountSettingsController::class)->only(['index', 'store']);

    Route::post('notifications/{notification}/update', [NotificationController::class, 'update'])->name('notification.update');

    Route::singleton('profile', ProfileController::class)->only(['edit', 'update', 'destroy'])->destroyable();
});


require __DIR__ . '/auth.php';
