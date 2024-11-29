<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsenceType;
use App\Models\User;
use App\Notifications\AbsenceNotification;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AbsenceController extends Controller
{

    public function index(Request $request)
    {
        Gate::authorize('publicAuth', User::class);

        $user = $request->user();

        return Inertia::render('Absence/AbsenceIndex', [
            'users' =>
            User::select('id', 'first_name', 'last_name', 'supervisor_id')
                ->inOrganization()
                ->with([
                    'absences' => fn($absence) => $absence->where('status', 'accepted')->select(['id', 'start', 'end', 'absence_type_id', 'user_id', 'status']),
                    'absences.absenceType:id,abbreviation',
                    "userWorkingWeeks:id,user_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday"
                ])
                ->get()->filter(fn($u) => $user->can('viewShow', $u))->map(fn($u) => [
                    ...$u->toArray(),
                    'absences' => $u->absences->filter(fn($a) => $user->can('viewShow', $a))->map(fn($a) => [
                        ...$a->toArray(),
                        'absence_type' => $user->can('viewShow', [AbsenceType::class, $u]) ? $a->absenceType : null,
                    ]),
                    'can' => [
                        'absence' => [
                            'create' => $user->can('create', [Absence::class, $u]),
                        ]
                    ]
                ]),
            'absence_types' => AbsenceType::inOrganization()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', [Absence::class, User::find($request['user_id'])]);

        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'absence_type_id' => 'required|exists:absence_types,id',
            'user_id' => ['required', 'exists:users,id', function (string $attribute, mixed $value, Closure $fail) {
                if (Auth::id() !== $value && User::find($value)->supervisor_id !== Auth::id()) {
                    $fail('Du bist nicht berechtigt dazu, diese Abwesenheit zu beantragen'); // TODO: middleware
                }
            }]
        ]);

        $user = User::find(Auth::id());

        $requires_approval = $user->supervisor_id && $user->supervisor_id != Auth::id() && AbsenceType::find($validated['absence_type_id'])->requires_aproval;

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
        Gate::authorize('delete', $absence);
        // TODO: 
    }
}
