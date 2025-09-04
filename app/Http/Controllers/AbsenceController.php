<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\AbsenceType;
use App\Models\User;
use App\Notifications\AbsenceDeleteNotification;
use App\Notifications\AbsenceNotification;
use App\Notifications\AbsencePatchNotification;
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
        ]);

        $date = array_key_exists('date', $validated) ? Carbon::parse($validated['date']) : Carbon::now();

        $absences = Absence::inOrganization()
            ->doesntHave('currentAcceptedPatch')
            ->whereIn('status', ['accepted', 'created'])
            ->where(fn($q) => $q->whereDate('start', '<=', $date->copy()->endOfMonth())->whereDate('end', '>=', $date->copy()->startOfMonth()))
            ->with([
                'absenceType' => fn($q) => $q->select(['id', 'abbreviation'])->withTrashed(),
                'user:id'
            ])
            ->withExists(['patches' => fn($q) => $q->where('status', 'created')])
            ->get(['id', 'start', 'end', 'absence_type_id', 'user_id', 'status']);

        $absencePatches = AbsencePatch::inOrganization()
            ->whereIn('status', ['accepted', 'created'])
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
                $authUser->can('viewShow', [Absence::class, $authUser->usersInOrganization->find($a->user_id)]) &&
                ($a->status === 'accepted' ||
                    ($a->status === 'created' &&
                        $a->user_id === $authUser->id ||
                        $authUser->can(
                            'update',
                            [Absence::class, $authUser->usersInOrganization->find($a->user_id)]
                        )
                    ))
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
            'users' => fn() => [...User::inOrganization()
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
                ->filter(fn($u) => $authUser->can('viewShow', [Absence::class, $u]))
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
                ])->toArray()],
            'absence_types' => fn() => AbsenceType::inOrganization()->get(['id', 'name', 'abbreviation', 'requires_approval']),
            'absences' =>  Inertia::merge(fn() => $absences),
            'absencePatches' =>  Inertia::merge(fn() => $absencePatches),
            'holidays' =>  Inertia::merge(fn() => $holidays->isEmpty() ? (object)[] : $holidays),
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
                if ($absenceUser->absences()->where('status', 'accepted')
                    ->where('start', '<=', $request['end'])
                    ->where('end', '>=', $request['start'])
                    ->exists()
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

        $absenceNotification = $authUser->notifications()
            ->where('type', AbsenceNotification::class)
            ->where('data->status', 'created')
            ->where('data->absence_id', $absence->id)
            ->first();

        if ($absenceNotification) {
            $absenceNotification->markAsRead();
            $absenceNotification->update(['data->status' => 'accepted']);
            $absence->user->notify(new DisputeStatusNotification($absence->user, $absence, $is_accepted ? 'accepted' : 'declined'));
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
                $absence->user->notify(new DisputeStatusNotification($absence->user, $absence, 'accepted', 'delete'));
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
            $absence->user->notify(new DisputeStatusNotification($absence->user, $absence, 'declined', 'delete'));
        }

        return back()->with('success', 'Der Antrag auf Löschung wurde abgelehnt.');
    }
}
