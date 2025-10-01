<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\AbsenceType;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\AbsenceDeleteNotification;
use App\Notifications\AbsenceNotification;
use App\Notifications\DisputeStatusNotification;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $date = Carbon::parse(Absence::find($validated['openAbsence'])->start);
        else if (array_key_exists('openAbsencePatch', $validated) && $validated["openAbsencePatch"] != null)
            $date = Carbon::parse(AbsencePatch::find($validated['openAbsencePatch'])->start);
        else if (array_key_exists('date', $validated)) $date =  Carbon::parse($validated['date']);


        $visibleUsers = User::inOrganization()
            ->where(function ($query) {
                $query->whereNull('resignation_date')
                    ->orWhere('resignation_date', '>=', now()->startOfYear());
            })
            ->get()
            ->filter(fn($u) =>  $authUser->can('viewShow', [Absence::class, $u]));

        $absences = Absence::inOrganization()
            ->doesntHave('currentAcceptedPatch')
            ->whereIn('user_id', $visibleUsers->pluck('id'))
            ->where(fn($q) => $q->whereDate('start', '<=', $date->copy()->endOfMonth())->whereDate('end', '>=', $date->copy()->startOfMonth()))
            ->with([
                'absenceType' => fn($q) => $q->select(['id', 'abbreviation'])->withTrashed(),
                'user:id'
            ])
            ->withExists(['patches' => fn($q) => $q->where('status', 'created')])
            ->get(['id', 'start', 'end', 'absence_type_id', 'user_id', 'status']);

        $absencePatches = AbsencePatch::inOrganization()
            ->whereIn('user_id', $visibleUsers->pluck('id'))
            ->where(fn($q) => $q->whereDate('start', '<=', $date->copy()->endOfMonth())->whereDate('end', '>=', $date->copy()->startOfMonth()))
            ->whereHas('log')
            ->with([
                'absenceType' => fn($q) => $q->select(['id', 'abbreviation'])->withTrashed(),
                'log' => fn($q) => $q->select(['id', 'user_id'])->withExists(['patches' => fn($q) => $q->where('status', 'created')]),
                'user:id'
            ])
            ->select(['id', 'start', 'end', 'absence_type_id', 'user_id', 'status', 'absence_id'])
            ->get();


        $absenceFilter =
            fn($a) => (
                ($a->status === 'accepted' ||
                    ($a->status === 'created' &&
                        $a->user_id === $authUser->id ||
                        $authUser->can(
                            'update',
                            [Absence::class, $visibleUsers->find($a->user_id)]
                        )
                    ) ||
                    ($a->status === 'declined' && $a->user_id === $authUser->id)
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

        $holidays = HolidayService::getHolidaysForMonth($authUser->operatingSite->country, $authUser->operatingSite->federal_state, $date)
            ->mapWithKeys(
                fn($val, $key) => [Carbon::parse($key)->format('Y-m-d') => $val]
            );

        return Inertia::render('Absence/AbsenceIndex', [
            'users' => fn() =>
            User::whereIn('id', $visibleUsers->pluck('id'))
                ->with([
                    'userWorkingWeeks:id,user_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday,active_since',
                    'operatingSite:id,country,federal_state',
                    'userLeaveDays',
                    'absences' => fn($q) => $q
                        ->doesntHave('currentAcceptedPatch')
                        ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
                        ->whereDate('start', '<=', $date->copy()->endOfYear())
                        ->whereDate('end', '>=', $date->copy()->startOfYear()),
                    'absencePatches' => fn($q) => $q
                        ->with('log.currentAcceptedPatch')
                        ->whereHas('absenceType', fn($q) => $q->where('type', 'Urlaub'))
                        ->where('status', 'accepted')
                        ->whereNot('type', 'delete')
                        ->whereDate('start', '<=', $date->copy()->endOfYear())
                        ->whereDate('end', '>=', $date->copy()->startOfYear())
                ])
                ->get(['id', 'first_name', 'last_name', 'supervisor_id', 'group_id', 'operating_site_id'])
                ->map(fn(User $u) => [
                    ...$u->toArray(),
                    'leaveDaysForYear' => $u->leaveDaysForYear($date, $u->userLeaveDays),
                    'usedLeaveDaysForYear' => $u->usedLeaveDaysForYear(
                        $date,
                        $u->userWorkingWeeks,
                        collect($u->absences)->merge($u->absencePatches->filter(fn($p) => $p->log->currentAcceptedPatch->is($p)))
                    ),
                    'can' => [
                        'absence' => [
                            'create' => $authUser->can('create', [Absence::class, $u]),
                            'update' => $authUser->can('update', [Absence::class, $u]),
                        ]
                    ]
                ])->values(),
            'absence_types' => fn() => AbsenceType::inOrganization()->get(['id', 'name', 'abbreviation', 'requires_approval']),
            'absences' =>  Inertia::merge(fn() => $absences),
            'absencePatches' =>  Inertia::merge(fn() => $absencePatches),
            'holidays' =>  Inertia::merge(fn() => $holidays->isEmpty() ? (object)[] : $holidays),
            'user_absence_filters' => $authUser->userAbsenceFilters,
            'date' => $date,
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
                    AbsencePatch::getCurrentEntries($absenceUser)
                    ->where('start', '<=', $request['end'])
                    ->where('end', '>=', $request['start'])
                    ->count() > 0
                )
                    $fail('In diesem Zeitraum besteht bereits eine Abwesenheit.');
            }],
            'end' => 'required|date|after_or_equal:start',
            'absence_type_id' => [
                'required',
                Rule::in(AbsenceType::inOrganization()->get()->pluck('id'))
            ],
            'user_id' => [
                'required',
                Rule::in(User::inOrganization()->get()->pluck('id'))
            ]
        ]);

        $requires_approval =
            $authUser->supervisor_id &&
            $authUser->supervisor_id != Auth::id() &&
            AbsenceType::find($validated['absence_type_id'])->requires_approval;

        $absence = Absence::create([
            ...$validated,
            'start' => Carbon::parse($validated['start']),
            'end' => Carbon::parse($validated['end']),
            'status' => 'created',
        ]);
        if ($authUser->id !== $absence->user_id) {
            $absence->user->notify(new DisputeStatusNotification($absence, $requires_approval ? 'created' : 'accepted'));
        }
        if ($requires_approval) $authUser->supervisor->notify(new AbsenceNotification($authUser, $absence));
        else $absence->accept();

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

        $absenceNotification = $authUser->notifications()
            ->where('type', AbsenceNotification::class)
            ->where('data->status', 'created')
            ->where('data->absence_id', $absence->id)
            ->first();

        if ($absenceNotification) {
            $absenceNotification->markAsRead();
            $absenceNotification->update(['data->status' => $is_accepted ? 'accepted' : 'declined']);
            $absence->user->notify(new DisputeStatusNotification($absence, $is_accepted ? 'accepted' : 'declined'));
        };

        if ($is_accepted) $absence->accept();
        else $absence->decline();

        return back()->with('success',  "Abwesenheit erfolgreich " . ($is_accepted ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroyDispute(Absence $absence, #[CurrentUser] User $authUser)
    {
        Gate::authorize('deleteDispute', $absence);

        $absence->deleteQuietly();

        $authUser->notifications()
            ->where('type', AbsenceNotification::class)
            ->where('data->status', 'created')
            ->where('data->absence_id', $absence->id)
            ->delete();

        return back()->with('success', 'Abwesenheitsantrag erfolgreich zurückgezogen');
    }

    public function destroy(Absence $absence, #[CurrentUser] User $authUser)
    {
        Gate::authorize('deleteRequestable',  $absence);

        if (Gate::allows('delete', $absence)) {
            $absence->delete();

            $openDeleteNotification = $authUser->notifications()
                ->where('type', AbsenceDeleteNotification::class)
                ->where('data->status', 'created')
                ->where('data->absence_id', $absence->id)
                ->first();

            if ($openDeleteNotification) {
                $openDeleteNotification->markAsRead();
                $openDeleteNotification->update(['data->status' => 'accepted']);
                $absence->user->notify(new DisputeStatusNotification($absence, 'accepted', 'delete'));
            }
            return back()->with('success', 'Die Abwesenheit wurde erfolgreich gelöscht.');
        } else {
            $authUser->supervisor->notify(new AbsenceDeleteNotification($authUser, $absence));
            return back()->with('success', 'Der Antrag auf Löschung wurder erfolgreich eingeleitet.');
        }
    }

    public function denyDestroy(Absence $absence, #[CurrentUser] User $authUser)
    {
        Gate::allows('delete', $absence);

        $openDeleteNotification = $authUser->notifications()
            ->where('type', AbsenceDeleteNotification::class)
            ->where('data->status', 'created')
            ->where('data->absence_id', $absence->id)
            ->first();

        if ($openDeleteNotification) {
            $openDeleteNotification->markAsRead();
            $openDeleteNotification->update(['data->status' => 'declined']);
            $absence->user->notify(new DisputeStatusNotification($absence, 'declined', 'delete'));
        }

        return back()->with('success', 'Der Antrag auf Löschung wurde abgelehnt.');
    }
}
