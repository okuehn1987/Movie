<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\User;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'workLogPatchRequests' => self::getWorkLogPatchRequests(),
        ]);
    }

    private function getWorkLogPatchRequests()
    {
        $authUser = request()->user();

        return [
            ...WorkLogPatch::inOrganization()
                ->where('status', 'created')
                ->whereHas('user')
                ->with(['log:id,start,end,is_home_office', 'user:id,first_name,last_name'])
                ->get(['id', 'start', 'end', 'is_home_office', 'user_id', 'work_log_id', 'comment'])
                ->filter(fn($patch) => $authUser->can('update', [WorkLogPatch::class, $patch->user]))
                ->toArray()
        ];
    }

    private function getAbsenceRequests()
    {
        $authUser = request()->user();

        $absenceRequests = Absence::inOrganization()
            ->where('status', 'created')
            ->whereHas('user')
            ->with(['user:id,first_name,last_name,operating_site_id', 'absenceType:id,name'])
            ->get(['id', 'start', 'end', 'user_id', 'absence_type_id']);

        $absenceRequestUsers = User::whereIn('id', $absenceRequests->pluck('user_id'))->with('operatingSite')->get();

        $absenceRequests =  [
            ...$absenceRequests
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
                ])->toArray()
        ];

        return $absenceRequests;
    }
    private function getAbsencePatchRequests()
    {
        $authUser = request()->user();

        $absencePatchRequests = AbsencePatch::inOrganization()
            ->where('status', 'created')
            ->whereHas('user')
            ->with(['user:id,first_name,last_name,operating_site_id,supervisor_id', 'absenceType:id,name'])
            ->get(['id', 'start', 'end', 'user_id', 'absence_type_id', 'absence_id']);

        $absenceRequestUsers = User::whereIn('id', $absencePatchRequests->pluck('user_id'))->with('operatingSite')->get();

        $absencePatchRequests =  [
            ...$absencePatchRequests
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
                ])->toArray()
        ];

        return $absencePatchRequests;
    }
}
