<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CheckIfGateWasUsedToAuthorizeRequest;
use App\Http\Middleware\HasOrganizationAccess;
use App\Http\Middleware\isApp;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', HasOrganizationAccess::class, CheckIfGateWasUsedToAuthorizeRequest::class])->group(function () {
    //super admin routes
    Route::resource('organization', OrganizationController::class)->only(['index', 'store', 'destroy']);
    Route::get('/organization/{organization}/tree', [OrganizationController::class, 'organigram'])->name('organization.tree');
    //super admin routes

    Route::post('/switchAppModule', [AppModuleController::class, 'switchAppModule'])->name('switchAppModule');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('reportBug', [BugReportController::class, 'store'])->name('reportBug.store');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::patch('organization/saveSettings', [OrganizationController::class, 'saveSettings'])->name('organization.saveSettings');
    Route::resource('organization', OrganizationController::class)->only(['show', 'update']);
    Route::post('/organization/{organization}', [OrganizationController::class, 'update'])->name('organization.update.post');
    Route::get('/organization/{organization}/getLogo', [OrganizationController::class, 'getLogo'])->name('organization.getLogo');

    Route::resource('absence', AbsenceController::class)->only(['index', 'store', 'destroy']);
    Route::delete('/absence/{absence}/denyDestroy', [AbsenceController::class, 'denyDestroy'])->name('absence.denyDestroy');
    Route::delete('/absence/{absence}/destroyDispute', [AbsenceController::class, 'destroyDispute'])->name('absence.destroyDispute');
    Route::patch('/absence/{absence}/updateStatus', [AbsenceController::class, 'updateStatus'])->name('absence.updateStatus');

    Route::resource('userAbsenceFilter', UserAbsenceFilterController::class)->only(['store', 'update', 'destroy'])->shallow();

    Route::resource('absence.absencePatch', AbsencePatchController::class)->only(['store', 'update', 'destroy'])->shallow();
    Route::patch('/absencePatch/{absencePatch}/updateStatus', [AbsencePatchController::class, 'updateStatus'])->name('absencePatch.updateStatus');

    Route::resource('dispute', DisputeController::class)->only(['index']);

    Route::resource('absenceType', AbsenceTypeController::class)->only(['store', 'update', 'destroy']);
    Route::resource('specialWorkingHoursFactor', SpecialWorkingHoursFactorController::class)->only(['store', 'update', 'destroy']);
    Route::resource('group', GroupController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('operatingSite', OperatingSiteController::class)->only(['index', 'store', 'destroy', 'update', 'show']);
    Route::resource('operatingSite.operatingTime', OperatingTimeController::class)->only(['store', 'destroy'])->shallow();

    Route::post('notifications/{notification}/update', [NotificationController::class, 'update'])->name('notification.update');

    Route::singleton('profile', ProfileController::class)->only(['update']);
    Route::post('profile', [ProfileController::class, 'updateSettings'])->name('profile.updateSettings');

    Route::resource('user', UserController::class)->only(['index', 'store', 'destroy', 'update']);
    Route::get('/user/{user}/generalInformation', [UserController::class, 'generalInformation'])->name('user.generalInformation');
    Route::get('/user/{user}/absences', [UserController::class, 'absences'])->name('user.absences');
    Route::get('/user/{user}/documents', [UserController::class, 'documents'])->name('user.documents');
    Route::get('/user/{user}/userOrganigram', [UserController::class, 'userOrganigram'])->name('user.userOrganigram');
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile');

    //herta specific routes
    Route::middleware(isApp::class . ':herta')->group(function () {
        Route::get('/user/{user}/timeAccounts', [UserController::class, 'timeAccounts'])->name('user.timeAccounts');
        Route::get('/user/{user}/timeAccountTransactions', [UserController::class, 'timeAccountTransactions'])->name('user.timeAccountTransactions');
        Route::get('/user/{user}/timeStatementDoc', [UserController::class, 'timeStatementDoc'])->name('user.timeStatementDoc');

        Route::resource('workLogPatch', WorkLogPatchController::class)->only(['destroy', 'store', 'update']);
        Route::resource('workLog', WorkLogController::class)->only(['index', 'store', 'destroy', 'update']);

        Route::post('/user/{user}/workLogs', [WorkLogController::class, 'createWorkLog'])->name('user.workLog.store');
        Route::get('/user/{user}/workLogs', [WorkLogController::class, 'userWorkLogs'])->name('user.workLog.index');
        Route::resource('travelLog', TravelLogController::class)->only(['store', 'update']);

        Route::resource('user.timeAccount', TimeAccountController::class)->shallow()->only(['store']);
        Route::resource('timeAccount', TimeAccountController::class)->only(['update', 'destroy']);
        Route::resource('timeAccountTransaction', TimeAccountTransactionController::class)->only(['store']);
        Route::resource('timeAccountSetting', TimeAccountSettingsController::class)->only(['store']);
    });

    //timesheets specific routes
    Route::middleware(isApp::class . ':timesheets')->group(function () {
        Route::resource('customer', CustomerController::class)->only(['index', 'store', 'update', 'show', 'destroy']);
        Route::resource('customer.customerNote', CustomerNoteController::class)->only(['store', 'update', 'destroy'])->shallow();
        Route::get('/customerNote/{customerNote}/getFile', [CustomerNoteController::class, 'getFile'])->name('customerNote.getFile');

        Route::resource('ticket', TicketController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('/ticket/{ticket}/finish', [TicketController::class, 'finish'])->name('ticket.finish');
        Route::resource('ticket.record', TicketRecordController::class)->only(['store', 'update', 'destroy'])->shallow();
    });
});


Route::get('/webmanifest', [ManifestController::class, 'getOrganizationManifest']);

require __DIR__ . '/auth.php';
