<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\HomeOfficeDay;
use App\Models\HomeOfficeDayGenerator;
use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use App\Notifications\AbsenceDeleteNotification;
use App\Notifications\HomeOfficeDeleteNotification;
use App\Services\AppModuleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DisputeController extends Controller
{
    public function index()
    {
        Gate::authorize('viewDisputes', User::class);

        return Inertia::render('Dispute/DisputeIndex', [
            'absenceRequests' => self::getAbsenceRequests(),
            'absencePatchRequests' => self::getAbsencePatchRequests(),
            'absenceDeleteRequests' => self::getAbsenceDeleteRequests(),
            'homeOfficeDayRequests' => self::getHomeOfficeDayRequests(),
            'homeOfficeDayDeleteRequests' => self::getHomeOfficeDayDeleteRequests(),
            ...(AppModuleService::hasAppModule('tide') ? [
                'workLogPatchRequests' => self::getWorkLogPatchRequests(),
                'workLogRequests' => self::getWorkLogRequests(),
            ] : [])
        ]);
    }

    private function getWorkLogPatchRequests(array $statuses = ['created'])
    {
        $authUser = request()->user();

        return WorkLogPatch::inOrganization()
            ->where('status', Status::Created)
            ->with([
                'log:id,start,end,is_home_office',
                'user' => fn($q) => $q->select(['id', 'first_name', 'last_name', 'operating_site_id', 'supervisor_id'])->withTrashed()
            ])
            ->get(['id', 'start', 'end', 'is_home_office', 'user_id', 'work_log_id', 'comment'])
            ->filter(fn($patch) => $authUser->can('update', [WorkLogPatch::class, $patch->user]))
            ->values();
    }

    private function getWorkLogRequests(array $statuses = ['created'])
    {
        $authUser = request()->user();

        return WorkLog::inOrganization()
            ->where('status', Status::Created)
            ->with([
                'user' => fn($q) => $q->select(['id', 'first_name', 'last_name', 'operating_site_id', 'supervisor_id'])->withTrashed()
            ])
            ->get(['id', 'start', 'end', 'is_home_office', 'user_id', 'comment'])
            ->filter(fn($log) => $authUser->can('update', [WorkLog::class, $log->user]))
            ->values();
    }

    private function getAbsenceRequests(array $statuses = ['created'])
    {
        $authUser = request()->user();

        $absenceRequests = Absence::inOrganization()
            ->where('status', Status::Created)
            ->with([
                'user' => fn($q) => $q->select(['id', 'first_name', 'last_name', 'operating_site_id', 'supervisor_id'])->withTrashed(),
                'absenceType:id,name'
            ])
            ->get(['id', 'start', 'end', 'user_id', 'absence_type_id']);

        $absenceRequestUsers = count($absenceRequests) > 0 ?
            User::whereIn('id', $absenceRequests->pluck('user_id'))->withTrashed()->with('operatingSite')->get() :
            collect();

        $absenceRequests = $absenceRequests
            ->filter(fn(Absence $a) => $authUser->can('update', [Absence::class, $absenceRequestUsers->find($a->user_id)]))
            ->map(fn(Absence $a) => [
                //FIXME: this triggers way too much queries
                ...$a->toArray(),
                'usedDays' => $a->usedDays,
                'user' => [
                    ...$a->user->toArray(),
                    'leaveDaysForYear' => $absenceRequestUsers->find($a->user_id)->leaveDaysForYear(Carbon::parse($a->start)),
                    'usedLeaveDaysForYear' => $absenceRequestUsers->find($a->user_id)->usedLeaveDaysForYear(Carbon::parse($a->start)),
                ]
            ])->values();

        return $absenceRequests;
    }
    private function getAbsencePatchRequests(array $statuses = ['created'])
    {
        $authUser = request()->user();

        $absencePatchRequests = AbsencePatch::inOrganization()
            ->where('status', Status::Created)
            ->with([
                'user' => fn($q) => $q->select(['id', 'first_name', 'last_name', 'operating_site_id', 'supervisor_id'])->withTrashed(),
                'absenceType:id,name'
            ])
            ->get(['id', 'start', 'end', 'user_id', 'absence_type_id', 'absence_id']);

        $absenceRequestUsers = count($absencePatchRequests) > 0 ?
            User::whereIn('id', $absencePatchRequests->pluck('user_id'))->withTrashed()->with('operatingSite')->get() :
            collect();

        $absencePatchRequests = $absencePatchRequests
            ->filter(fn(AbsencePatch $a) => $authUser->can('update', $a))
            ->map(fn(AbsencePatch $a) => [
                //FIXME: this triggers way too much queries
                ...$a->toArray(),
                'usedDays' => $a->usedDays,
                'user' => [
                    ...$a->user->toArray(),
                    'leaveDaysForYear' => $absenceRequestUsers->find($a->user_id)->leaveDaysForYear(Carbon::parse($a->start)),
                    'usedLeaveDaysForYear' => $absenceRequestUsers->find($a->user_id)->usedLeaveDaysForYear(Carbon::parse($a->start)),
                ]
            ])->values();

        return $absencePatchRequests;
    }

    public function getAbsenceDeleteRequests(array $statuses = ['created'])
    {
        $authUser = request()->user();

        $openDeleteNotifications = $authUser->notifications()
            ->where('type', AbsenceDeleteNotification::class)
            ->where('data->status', Status::Created)
            ->get();

        $requestedAbsences = count($openDeleteNotifications) > 0 ?
            Absence::inOrganization()
            ->whereIn('id', $openDeleteNotifications->pluck('data.absence_id'))
            ->with([
                'user' => fn($q) => $q->select(['id', 'first_name', 'last_name', 'operating_site_id', 'supervisor_id'])->withTrashed(),
                'absenceType:id,name'
            ])
            ->get(['id', 'start', 'end', 'user_id', 'absence_type_id']) :
            collect();

        return $requestedAbsences->filter(fn(Absence $a) => $authUser->can('delete', $a))->values();
    }

    public function getHomeOfficeDayRequests()
    {
        $authUser = request()->user();

        return HomeOfficeDayGenerator::inOrganization()
            ->whereHas('homeOfficeDays', fn($q) => $q->where('status', Status::Created))
            ->with([
                'user' => fn($q) => $q->select(['id', 'first_name', 'last_name', 'operating_site_id', 'supervisor_id'])->withTrashed()
            ])
            ->get(['id', 'user_id', 'start', 'end'])
            ->filter(fn($log) => $authUser->can('update', [Absence::class, $log->user]))
            ->values();
    }

    public function getHomeOfficeDayDeleteRequests()
    {
        $authUser = request()->user();
        $openHomeOfficeDayDeleteNotifications = $authUser->notifications()
            ->where('type', HomeOfficeDeleteNotification::class)
            ->where('data->status', Status::Created)
            ->get();

        $requestedHomeOfficeDays = count($openHomeOfficeDayDeleteNotifications) > 0 ?
            HomeOfficeDay::inOrganization()
            ->whereIn('id', $openHomeOfficeDayDeleteNotifications->pluck('data.home_office_day_id'))
            ->with([
                'user' => fn($q) => $q->select(['id', 'first_name', 'last_name', 'operating_site_id', 'supervisor_id'])->withTrashed(),
            ])
            ->get(['id', 'date', 'user_id'])
            : collect();

        return $requestedHomeOfficeDays->filter(fn(HomeOfficeDay $homeOfficeDay) => $authUser->can('deleteHomeOffice', [Absence::class, $homeOfficeDay]))->values();
    }
}
