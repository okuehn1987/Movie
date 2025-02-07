<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsenceType;
use App\Models\User;
use App\Notifications\AbsenceNotification;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AbsenceController extends Controller
{

    public function index(Request $request)
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = array_key_exists('date', $validated) ? Carbon::parse($validated['date'])  : Carbon::now();

        $user = $request->user();

        $absences = [...Absence::inOrganization()->whereIn('status', ['accepted', 'created'])
            ->where(fn($q) => $q->where('start', '<=', $date->copy()->endOfMonth())->where('end', '>=', $date->copy()->startOfMonth()))
            ->with(['absenceType' => fn($q) => $q->select(['id', 'abbreviation'])->withTrashed(), 'user:id,group_id,operating_site_id,supervisor_id'])
            ->get(['id', 'start', 'end', 'absence_type_id', 'user_id', 'status'])
            ->filter(
                fn($a) =>
                $user->can('viewShow', [Absence::class, $a->user]) &&
                    ($a->status === 'accepted' ||
                        ($a->status === 'created' &&
                            $a->user_id === $user->id ||
                            $user->can(
                                'update',
                                [Absence::class, $a->user]
                            )
                        ))
            )
            ->toArray()];

        $holidays = HolidayService::getHolidaysForMonth($user->operatingSite->country, $user->operatingSite->federal_state, $date)
            ->mapWithKeys(
                fn($val, $key) => [Carbon::parse($key)->format('Y-m-d') => $val]
            );

        return Inertia::render('Absence/AbsenceIndex', [
            'users' => fn() => [...User::inOrganization()
                ->with([
                    'userWorkingWeeks:id,user_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday'
                ])
                ->get(['id', 'first_name', 'last_name', 'supervisor_id', 'group_id'])
                ->filter(fn($u) => $user->can('viewShow', [Absence::class, $u]))
                ->map(fn($u) => [
                    ...$u->toArray(),
                    'can' => [
                        'absence' => [
                            'create' => $user->can('create', [Absence::class, $u]),
                        ]
                    ]
                ])->toArray()],
            'absence_types' => fn() => AbsenceType::inOrganization()->get(['id', 'name', 'abbreviation']),
            'absences' =>  Inertia::merge(fn() => $absences),
            'holidays' =>  Inertia::merge(fn() => $holidays->isEmpty() ? (object)[] : $holidays)
        ]);
    }

    public function store(Request $request)
    {
        $absenceUser = User::find($request['user_id']);

        Gate::authorize('create', [Absence::class, $absenceUser]);

        $validated = $request->validate([
            'start' => ['required', 'date', 'before_or_equal:end', function ($attr, $val, $fail) use ($absenceUser, $request) {
                if ($absenceUser->absences()->where('status', 'accepted')
                    ->where('start', '<=', $request['end'])
                    ->where('end', '>=', $request['start'])
                    ->exists()
                )
                    $fail('In diesem Zeitraum besteht bereits eine Abwesenheit.');
            }],
            'end' => 'required|date|after_or_equal:start',
            'absence_type_id' => 'required|exists:absence_types,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find(Auth::id());

        $requires_approval =
            $user->supervisor_id &&
            $user->supervisor_id != Auth::id() &&
            AbsenceType::find($validated['absence_type_id'])->requires_approval;

        $absence = Absence::create([
            ...$validated,
            'start' => Carbon::parse($validated['start']),
            'end' => Carbon::parse($validated['end']),
            'status' => $requires_approval ? 'created' : 'accepted',
        ]);

        if ($absence->status == 'accepted') $absence->accountAsTransaction();

        if ($requires_approval) $user->supervisor->notify(new AbsenceNotification($user, $absence));

        return back()->with('success', 'Abwesenheit erfolgreich beantragt.');
    }

    // public function update(Request $request, Absence $absence)
    // {
    //     // TODO: implement with e2e test
    //     $absenceUser = User::find($request['user_id']);
    //     Gate::authorize('update', [Absence::class, $absenceUser]);
    // }

    public function updateStatus(Request $request, Absence $absence)
    {
        Gate::authorize('update', [Absence::class, $absence->user]);

        $validated = $request->validate([
            'accepted' => 'required|boolean'
        ]);

        $absenceNotification = $request->user()
            ->unreadNotifications()
            ->where('notifiable_id', Auth::id())
            ->where('data->absence_id', $absence->id)->first();

        if ($absenceNotification) $absenceNotification->update(['read_at' => Carbon::now()]);

        if ($validated['accepted']) {
            $absence->accountAsTransaction();
        } else {
            $absence->update(['status' => 'declined']);
        }

        return back()->with('success',  "Abwesenheit erfolgreich " . ($validated['accepted'] ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroy(Absence $absence)
    {
        // TODO: 
    }
}
