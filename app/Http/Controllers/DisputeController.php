<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DisputeController extends Controller
{
    public function index(Request $request, #[CurrentUser] User $authUser)
    {
        $absenceRequests = [...Absence::inOrganization()
            ->where('status', 'created')
            ->with(['user:id,first_name,last_name,operating_site_id', 'absenceType:id,name'])
            ->get(['id', 'start', 'end', 'user_id', 'absence_type_id'])
            ->filter(fn(Absence $a) => $authUser->can('update', [Absence::class, $a->user]))
            ->map(fn(Absence $a) => [
                //FIXME: this triggers way too much queries
                ...$a->toArray(),
                'usedDays' => $a->usedDays,
                'user' => [
                    ...$a->user->toArray(),
                    'leaveDaysForYear' => $a->user->leaveDaysForYear(Carbon::parse($a->start)),
                    'usedLeaveDaysForYear' => $a->user->usedLeaveDaysForYear(Carbon::parse($a->start)),
                ]
            ])->toArray()];

        $patches = [...WorkLogPatch::inOrganization()
            ->where('status', 'created')
            ->with(['log:id,start,end,is_home_office', 'user:id,first_name,last_name'])
            ->get(['id', 'start', 'end', 'is_home_office', 'user_id', 'work_log_id', 'comment'])
            ->filter(fn($patch) => $authUser->can('update', [WorkLogPatch::class, $patch->user]))
            ->toArray()];

        return Inertia::render('Dispute/DisputeIndex', [
            'absenceRequests' => $absenceRequests,
            'patches' => $patches,
        ]);
    }
}
