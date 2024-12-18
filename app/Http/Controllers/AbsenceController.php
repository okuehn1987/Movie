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

        $absences = Absence::inOrganization()->where('status', 'accepted')
            ->where(fn($q) => $q->where('start', '<=', $date->copy()->endOfMonth())->where('end', '>=', $date->copy()->startOfMonth()))
            ->with(['absenceType:id,abbreviation', 'user:id,group_id,operating_site_id,supervisor_id'])
            ->get(['id', 'start', 'end', 'absence_type_id', 'user_id', 'status'])
            ->filter(fn($a) => $user->can('viewShow', [Absence::class, $a->user]));


        $holidays = collect(HolidayService::getHolidays($user->operatingSite->country, $user->operatingSite->federal_state, $date))
            ->mapWithKeys(
                fn($val, $key) => [Carbon::parse($key)->format('Y-m-d') => $val]
            );

        return Inertia::render('Absence/AbsenceIndex', [
            'users' => fn() => User::inOrganization()
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
                ]),
            'absence_types' => fn() => AbsenceType::inOrganization()->get(['id', 'name', 'abbreviation']),
            'absences' =>  Inertia::merge(fn() => $absences),
            'holidays' =>  Inertia::merge(fn() => $holidays->isEmpty() ? (object)[] : $holidays)
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', [Absence::class, User::find($request['user_id'])]);

        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'absence_type_id' => 'required|exists:absence_types,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find(Auth::id());

        $requires_approval = $user->supervisor_id && $user->supervisor_id != Auth::id() && AbsenceType::find($validated['absence_type_id'])->requires_approval;

        $absence = Absence::create([
            ...$validated,
            'start' => Carbon::parse($validated['start']),
            'end' => Carbon::parse($validated['end']),
            'status' => $requires_approval ? 'created' : 'accepted',
        ]);

        if ($requires_approval) $user->supervisor->notify(new AbsenceNotification($user, $absence));

        return back()->with('success', 'Abwesenheit beantragt.');
    }

    public function update(Request $request, Absence $absence)
    {
        Gate::authorize('update', $absence);

        $validated = $request->validate([
            'accepted' => 'required|boolean'
        ]);

        $absence->update(['status' => $validated['accepted'] ? 'accepted' : 'declined']);

        return back()->with('success',  "Abwesenheit erfolgreich " . ($validated['accepted'] ? 'akzeptiert' : 'abgelehnt') . ".");
    }

    public function destroy(Absence $absence)
    {
        // TODO: 
    }
}
