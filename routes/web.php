<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckIfGateWasUsedToAuthorizeRequest;
use App\Http\Middleware\HasOrganizationAccess;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', HasOrganizationAccess::class, CheckIfGateWasUsedToAuthorizeRequest::class])->group(function () {
    //super admin routes
    Route::resource('organization', OrganizationController::class)->only(['index', 'store', 'destroy']);
    Route::get('/organization/{organization}/tree', [OrganizationController::class, 'organigram'])->name('organization.tree');
    //super admin routes

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('user', UserController::class)->only(['index', 'store', 'destroy', 'update', 'show']);

    Route::resource('organization', OrganizationController::class)->only(['show', 'update']);
    Route::resource('absence', AbsenceController::class)->only(['index', 'update', 'store']);
    Route::resource('absenceType', AbsenceTypeController::class)->only(['store', 'update', 'destroy']);
    Route::resource('specialWorkingHoursFactor', SpecialWorkingHoursFactorController::class)->only(['store', 'update', 'destroy']);
    Route::resource('group', GroupController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('workLogPatch', WorkLogPatchController::class)->only(['destroy', 'store', 'update']);
    Route::resource('workLog', WorkLogController::class)->only(['index', 'store']);
    Route::get('/user/{user}/workLogs', [WorkLogController::class, 'userWorkLogs'])->name('user.workLog.index');
    Route::resource('travelLog', TravelLogController::class)->only(['store', 'update']);

    Route::resource('operatingSite', OperatingSiteController::class)->only(['index', 'store', 'destroy', 'update', 'show']);
    Route::resource('operatingTime', OperatingTimeController::class)->only(['store', 'destroy']);

    Route::resource('user.timeAccount', TimeAccountController::class)->shallow()->only(['store']);
    Route::resource('timeAccount', TimeAccountController::class)->only(['update', 'destroy']);
    Route::resource('timeAccountTransaction', TimeAccountTransactionController::class)->only(['store']);
    Route::resource('timeAccountSetting', TimeAccountSettingsController::class)->only(['store']);

    Route::get('dispute', [DisputeController::class, 'index'])->name('dispute.index');

    Route::post('notifications/{notification}/update', [NotificationController::class, 'update'])->name('notification.update');

    Route::singleton('profile', ProfileController::class)->only(['update']);
});


require __DIR__ . '/auth.php';
