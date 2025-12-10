<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\AbsenceType;
use App\Enums\Status;
use App\Models\HomeOfficeDay;
use App\Models\OperatingSite;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\AbsenceDeleteNotification;
use App\Notifications\AbsenceNotification;
use App\Notifications\DisputeStatusNotification;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AbsenceController extends Controller
{

    public function index(Request $request, #[CurrentUser] User $authUser)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'date' => 'nullable|date',
            'openAbsence' => [
                'nullable',
                Rule::exists('absences', 'id')->whereIn(
                    'absence_type_id',
                    AbsenceType::where('organization_id', Organization::getCurrent()->id)
                        ->select('id')
                )
            ],
            'openAbsencePatch' => [
                'nullable',
                Rule::exists('absence_patches', 'id')->whereIn(
                    'absence_type_id',
                    AbsenceType::where('organization_id', Organization::getCurrent()->id)
                        ->select('id')
                )
            ]
        ]);

        $date = Carbon::now();
        if (array_key_exists('openAbsence', $validated) && $validated["openAbsence"] != null)
            $date = Carbon::parse(Absence::find($validated['openAbsence'])?->start);
        else if (array_key_exists('openAbsencePatch', $validated) && $validated["openAbsencePatch"] != null)
            $date = Carbon::parse(AbsencePatch::find($validated['openAbsencePatch'])?->start);
        else if (array_key_exists('date', $validated)) $date =  Carbon::parse($validated['date']);


        $visibleUsers = User::inOrganization()
            ->where(
                fn($query) =>
                $query->whereNull('resignation_date')
                    ->orWhere('resignation_date', '>=', $date->copy()->startOfYear())
            )
            ->get()
            ->filter(fn($u) =>  $authUser->can('viewShow', [Absence::class, $u]));

        $homeOfficeDays = HomeOfficeDay::inOrganization()
            ->whereIn('user_id', $visibleUsers->pluck('id'))
            ->whereBetween('date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->with('homeOfficeDayGenerator:id,start,end,created_as_request')
            ->get(['id', 'user_id', 'date', 'status', 'home_office_day_generator_id']);

        $absences = Absence::inOrganization()
            ->doesntHave('currentAcceptedPatch')
            ->whereIn('user_id', $visibleUsers->pluck('id'))
            ->where(fn($q) => $q->whereDate('start', '<=', $date->copy()->endOfMonth())->whereDate('end', '>=', $date->copy()->startOfMonth()))
            ->with([
                'absenceType' => fn($q) => $q->select(['id', 'abbreviation'])->withTrashed(),
                'user:id'
            ])
            ->withExists(['patches' => fn($q) => $q->where('status', Status::Created)])
            ->get(['id', 'start', 'end', 'absence_type_id', 'user_id', 'status']);

        $absencePatches = AbsencePatch::inOrganization()
            ->whereIn('user_id', $visibleUsers->pluck('id'))
            ->where(fn($q) => $q->whereDate('start', '<=', $date->copy()->endOfMonth())->whereDate('end', '>=', $date->copy()->startOfMonth()))
            ->whereHas('log')
            ->with([
                'absenceType' => fn($q) => $q->select(['id', 'abbreviation'])->withTrashed(),
                'log' => fn($q) => $q->select(['id', 'user_id'])->withExists(['patches' => fn($q) => $q->where('status', Status::Created)]),
                'user:id'
            ])
            ->select(['id', 'start', 'end', 'absence_type_id', 'user_id', 'status', 'absence_id'])
            ->get();


        $absenceFilter =
            fn($a) => (
                ($a->status === Status::Accepted ||
                    ($a->status === Status::Created &&
                        $a->user_id === $authUser->id ||
                        $authUser->can(
                            'update',
                            [Absence::class, $visibleUsers->find($a->user_id)]
                        )
                    ) ||
                    ($a->status === Status::Declined && $a->user_id === $authUser->id)
                )
            );

        $absences = $absences->filter($absenceFilter)->map(fn(Absence $a) => [
            ...$a->toArray(),
            'can' => [
                'absence' => [
                    'deleteDispute' => $authUser->can('deleteDispute', [Absence::class, $a])
                ]
            ]
        ])->values();

        $absencePatches = $absencePatches->filter($absenceFilter)->map(fn($ap) => [
            ...$ap->toArray(),
            'can' => [
                'absence' => [
                    'deleteDispute' => $authUser->can('delete', [AbsencePatch::class, $ap])
                ]
            ]
        ])->values();

        $holidays = HolidayService::getHolidaysForMonth(
            $authUser->operatingSite->currentAddress->country,
            $authUser->operatingSite->currentAddress->federal_state,
            $date
        );

        $schoolHolidays = HolidayService::getSchoolHolidaysForMonth($date);

        return Inertia::render('Absence/AbsenceIndex', [
            'users' => fn() =>
            User::whereIn('id', $visibleUsers->pluck('id'))
                ->with([
                    'userWorkingWeeks:id,user_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday,active_since',
                    'operatingSite.currentAddress:id,country,federal_state,addresses.addressable_id,addresses.addressable_type',
                    'userLeaveDays',
                    'absences' => fn($q) => $q
                        ->doesntHave('currentAcceptedPatch')
                        ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
                        ->whereIn('status', [Status::Accepted, Status::Created])
                        ->whereDate('end', '>=', now()->startOfYear()),
                    'absencePatches' => fn($q) => $q
                        ->with('log.currentAcceptedPatch')
                        ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
                        ->whereIn('status', [Status::Accepted, Status::Created])
                        ->whereNot('type', 'delete')
                        ->whereDate('end', '>=', now()->startOfYear())
                ])
                ->get(['id', 'first_name', 'last_name', 'supervisor_id', 'group_id', 'operating_site_id', 'home_office'])
                ->map(function (User $u) use ($authUser) {
                    $allEntries = collect($u->absences)->merge($u->absencePatches);
                    $allAbsenceYears = $allEntries->flatMap(
                        fn($a) =>
                        [Carbon::parse($a->start), Carbon::parse($a->end)]
                    )
                        ->merge($u->userLeaveDays->map(fn($ld) => Carbon::parse($ld->active_since)))
                        ->merge($u->userLeaveDays->isNotEmpty() ? [Carbon::parse($u->userLeaveDays->max('active_since'))->addYear()] : [])
                        ->merge([now()])
                        ->unique(fn($e) => $e->year);
                    $usedLeaveDaysForYear = [];
                    $leaveDaysForYear = (object)[];
                    foreach ($allAbsenceYears as $year) {
                        $leaveDaysForYear->{$year->year} =  $u->leaveDaysForYear($year, $u->userLeaveDays);

                        $usedLeaveDaysForYear = $usedLeaveDaysForYear + $u->usedLeaveDaysForYear(
                            $year,
                            $u->userWorkingWeeks,
                            collect($u->absences)->merge($u->absencePatches->filter(fn($p) => $p->log->currentAcceptedPatch->is($p)))
                        );
                    }
                    if (count($usedLeaveDaysForYear) == 0) $usedLeaveDaysForYear = (object)[];
                    return [
                        ...$u->toArray(),
                        'leaveDaysForYear' => $leaveDaysForYear,
                        'usedLeaveDaysForYear' => $usedLeaveDaysForYear,
                        'can' => [
                            'absence' => [
                                'create' => $authUser->can('create', [Absence::class, $u]),
                                'update' => $authUser->can('update', [Absence::class, $u]),
                            ]
                        ]
                    ];
                })->values(),
            'absenceTypes' => fn() => AbsenceType::inOrganization()->get(['id', 'name', 'abbreviation', 'requires_approval']),
            'absences' =>  Inertia::merge(fn() => $absences),
            'absencePatches' =>  Inertia::merge(fn() => $absencePatches),
            'holidays' =>  Inertia::merge(fn() => $holidays->isEmpty() ? (object)[] : $holidays),
            'userAbsenceFilters' => $authUser->userAbsenceFilters,
            'homeOfficeDays' => Inertia::merge(fn() => $homeOfficeDays),
            'schoolHolidays' =>  Inertia::merge(fn() => $schoolHolidays->isEmpty() ? (object)[] : [$date->format('Y-m') => $schoolHolidays]),
            'date' => $date,
            'federal_state' => $authUser->operatingSite->currentAddress->federal_state,
            'all_federal_states' => HolidayService::$COUNTRIES['DE']['regions'],
            'can' => [
                'user' => [
                    'viewDisputes' => $authUser->can('viewDisputes', User::class),
                ]
            ],
        ]);
    }

    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        $absenceUser = User::find($request['user_id']);

        Gate::authorize('create', [Absence::class, $absenceUser]);

        $validated = $request->validate([
            'start' => ['required', 'date', function ($attr, $val, $fail) use ($absenceUser, $request) {
                if (
                    AbsencePatch::getCurrentEntries($absenceUser, true)
                    ->where('start', '<=', $request['end'])
                    ->where('end', '>=', $request['start'])
                    ->count() > 0
                )
                    $fail('In diesem Zeitraum besteht bereits eine Abwesenheit.');
            }],
            'end' => 'required|date|after_or_equal:start',
            'absence_type_id' => [
                'required',
                Rule::exists('absence_types', 'id')->whereIn('id', AbsenceType::inOrganization()->select('id'))
            ],
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->whereIn('operating_site_id', OperatingSite::inOrganization()->select('id'))
            ]
        ]);

        $requires_approval = $authUser->supervisor_id && AbsenceType::find($validated['absence_type_id'])->requires_approval;

        $absence = Absence::create([
            ...$validated,
            'start' => Carbon::parse($validated['start']),
            'end' => Carbon::parse($validated['end']),
            'status' => Status::Created,
        ]);

        if ($authUser->id !== $absence->user_id) {
            $absence->user->notify(new DisputeStatusNotification($absence, $requires_approval ? Status::Created : Status::Accepted));
        }
        if ($requires_approval) {
            collect($authUser->supervisor->loadMissing('isSubstitutedBy')->isSubstitutedBy)
                ->merge([$authUser->supervisor])
                ->unique('id')
                ->each
                ->notify(new AbsenceNotification($authUser, $absence));
        } else $absence->accept();

        return back()->with('success', 'Abwesenheit erfolgreich beantragt.');
    }

    public function updateStatus(Request $request, Absence $absence, #[CurrentUser] User $authUser)
    {
        Gate::authorize('update', [Absence::class, $absence->user]);

        $is_accepted = $request->validate([
            'accepted' => 'required|boolean'
        ])['accepted'];

        if (
            $is_accepted &&
            AbsencePatch::getCurrentEntries($absence->user)
            ->where('start', '<=', $absence->end)
            ->where('end', '>=', $absence->start)
            ->count() > 0
        ) return back()->with('error', 'In diesem Zeitraum besteht bereits eine Abwesenheit.');

        DB::table('notifications')->where('type', AbsenceNotification::class)
            ->where('data->status', Status::Created)
            ->where('data->absence_id', $absence->id)
            ->update([
                'read_at' => now(),
                'data->status' => $is_accepted ? Status::Accepted : Status::Declined
            ]);

        if ($absence->user->id !== $authUser->id) {
            $absence->user->notify(new DisputeStatusNotification($absence, $is_accepted ? Status::Accepted : Status::Declined));
        }

        if ($is_accepted) $absence->accept();
        else $absence->decline();

        return back()->with('success',  "Abwesenheit erfolgreich " . ($is_accepted ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroyDispute(Absence $absence)
    {
        Gate::authorize('deleteDispute', $absence);

        if ($absence->deleteQuietly()) {
            DB::table('notifications')->where('type', AbsenceNotification::class)
                ->where('data->status', Status::Created)
                ->where('data->absence_id', $absence->id)
                ->delete();
        }

        return back()->with('success', 'Abwesenheitsantrag erfolgreich zurückgezogen');
    }

    public function destroy(Absence $absence, #[CurrentUser] User $authUser)
    {
        Gate::authorize('deleteRequestable',  $absence);

        if (Gate::allows('delete', $absence)) {
            $absence->delete();

            DB::table('notifications')->where('type', AbsenceDeleteNotification::class)
                ->where('data->status', Status::Created)
                ->where('data->absence_id', $absence->id)
                ->update([
                    'read_at' => now(),
                    'data->status' => Status::Accepted
                ]);

            if ($absence->user->id !== $authUser->id)
                $absence->user->notify(new DisputeStatusNotification($absence, Status::Accepted, 'delete'));

            return back()->with('success', 'Die Abwesenheit wurde erfolgreich gelöscht.');
        } else {
            collect($authUser->supervisor->loadMissing('isSubstitutedBy')->isSubstitutedBy)
                ->merge([$authUser->supervisor])
                ->unique('id')
                ->each
                ->notify(new AbsenceDeleteNotification($authUser, $absence));
            return back()->with('success', 'Der Antrag auf Löschung wurder erfolgreich eingeleitet.');
        }
    }

    public function denyDestroy(Absence $absence, #[CurrentUser] User $authUser)
    {
        Gate::allows('delete', $absence);

        DB::table('notifications')->where('type', AbsenceDeleteNotification::class)
            ->where('data->status', Status::Created)
            ->where('data->absence_id', $absence->id)
            ->update([
                'read_at' => now(),
                'data->status' => Status::Declined
            ]);

        if ($absence->user->id !== $authUser->id)
            $absence->user->notify(new DisputeStatusNotification($absence, Status::Declined, 'delete'));

        return back()->with('success', 'Der Antrag auf Löschung wurde abgelehnt.');
    }
}
