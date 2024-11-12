<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\AbsenceType;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AbsenceController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Absence/AbsenceIndex', [
            'users' =>
            User::select('id', 'first_name', 'last_name', 'supervisor_id')
                ->inOrganization()
                ->where('group_id', $user->group_id)
                ->orWhere('supervisor_id', $user->id)
                ->with('absences:id,start,end,absence_type_id,user_id,status', 'absences.absenceType:id,abbreviation', "userWorkingWeeks:id,user_id,monday,tuesday,wednesday,thursday,friday,saturday,sunday")
                ->get(),
            'absence_types' => AbsenceType::inOrganization()->get(['id', 'name'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
            'absence_type_id' => 'exists:absence_types,id',
            'user_id' => ['required', 'exists:users,id', function (string $attribute, mixed $value, Closure $fail) {
                if (Auth::id() !== $value || User::find($value)->supervisor_id !== Auth::id()) {
                    $fail('Du bist nicht berechtigt dazu, diese Abwesenheit zu beantragen'); // TODO: middleware
                }
            }]
        ]);

        Absence::create([
            ...$validated,
            'start' => Carbon::parse($validated['start']),
            'end' => Carbon::parse($validated['end']),
            'status' => AbsenceType::find($validated['absence_type_id'])->requires_aproval ? 'created' : 'accepted',
        ]);

        return back()->with('success', 'Abwesenheit beantragt.');
    }
}
