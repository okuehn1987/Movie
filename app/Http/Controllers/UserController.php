<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\OperatingSite;
use App\Models\User;
use App\Models\UserWorkingHour;
use App\Models\UserWorkingWeek;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('User/UserIndex', [
            'users' => User::inOrganization()->with('group:id,name')->paginate(12),
            'permissions' => User::$PERMISSIONS,
            'groups' => Group::inOrganization()->get(['id', 'name']),
            'operating_sites' => OperatingSite::inOrganization()->get(['id', 'name'])
        ]);
    }

    public function show(User $user)
    {
        $user['lastWorkingHours'] = $user->userWorkingHours()->latest()->first();
        $user['userWorkingWeek'] = $user->userWorkingWeeks()->latest()->first();
        return Inertia::render('User/UserShow', [
            'user' => $user,
            'groups' => Group::inOrganization()->get(),
            'operating_sites' => OperatingSite::inOrganization()->get(),
            'permissions' => User::$PERMISSIONS,
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            "first_name" => "required|string",
            "last_name" => "required|string",
            "email" => "required|string|unique:users",
            "date_of_birth" => "nullable|date",
            "city" => "nullable|string",
            "zip" => "nullable|string",
            "street" => "nullable|string",
            "house_number" => "nullable|string",
            "address_suffix" => "nullable|string",
            "country" => "nullable|string",
            "federal_state" => "nullable|string",
            "phone_number" => "nullable|string",
            "staff_number" => "nullable|integer",
            "password" => "required|string",
            "group_id" => "nullable|integer",
            'operating_site_id' => 'required|integer',
            'userWorkingHours' => 'required|integer',
            'userWorkingHoursSince' => 'required|date',
            'userWorkingWeek' => 'required|array',
            'userWorkingWeek.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'userWorkingWeekSince' => 'required|date',
            'permissions' => 'nullable|array',
            'permissions.*' => ['required', Rule::in(collect(User::$PERMISSIONS)->map(fn($p) => $p['name']))]
        ]);

        $user = new User([
            ...$validated,
            'date_of_birth' => Carbon::parse($validated['date_of_birth']),
            'email_verified_at' => now(),
        ]);
        $user->password = Hash::make($validated['password']);

        $user->save();

        UserWorkingHour::create([
            'user_id' => $user->id,
            'weekly_working_hours' => $validated['weekly_working_hours'],
        ]);

        UserWorkingWeek::create([
            'user_id' => $user->id,
            ...collect($validated['userWorkingWeek'])->flatMap(fn($e) => [$e => true]),
            'active_since' => Carbon::parse($validated['userWorkingWeekSince'])
        ]);

        foreach ($validated['permissions'] as $permission) {
            $user[$permission] = true;
        }



        return back()->with('success', 'User erfolgreich angelegt.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return back()->with('success', 'User erfolgreich gelöscht.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            "first_name" => "required|string",
            "last_name" => "required|string",
            "email" => "required|string",
            "date_of_birth" => "nullable|date",
            "city" => "nullable|string",
            "zip" => "nullable|string",
            "street" => "nullable|string",
            "house_number" => "nullable|string",
            "address_suffix" => "nullable|string",
            "country" => "nullable|string",
            "federal_state" => "nullable|string",
            "phone_number" => "nullable|string",
            "staff_number" => "nullable|integer",
            "group_id" => "nullable|integer",
            'operating_site_id' => 'required|integer',
            'userWorkingHours' => 'required|integer',
            'userWorkingHoursSince' => 'required|date',
            'userWorkingWeek' => 'required|array',
            'userWorkingWeek.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'userWorkingWeekSince' => 'required|date',
            'permissions' => 'nullable|array',
            'permissions.*' => ['required', Rule::in(collect(User::$PERMISSIONS)->map(fn($p) => $p['name']))]
        ]);

        $user->update([
            ...$validated,
            'date_of_birth' => Carbon::parse(Carbon::parse($validated['date_of_birth'])->format('d-m-Y')),
            ...collect(User::$PERMISSIONS)->flatMap(fn($p) => [$p['name'] => in_array($p['name'], $validated['permissions'])])
        ]);

        $lastWorkingHour = $user->userWorkingHours()
            ->where('active_since', Carbon::parse($validated['userWorkingHoursSince']))
            ->latest()
            ->firstOrNew();

        $lastWorkingHour->fill([
            'weekly_working_hours' => floatval($validated['userWorkingHours']),
            'active_since' => Carbon::parse($validated['userWorkingHoursSince'])->format("Y-m-d")
        ]);

        if ($lastWorkingHour->isDirty()) $lastWorkingHour->replicate()->save();

        $workingWeek = $user->userWorkingWeeks()
            ->where('active_since', Carbon::parse($validated['userWorkingWeekSince']))
            ->latest()
            ->firstOrNew();

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $workingWeek[$day] = in_array($day, $validated['userWorkingWeek']) ? 1 : 0;
        }

        if ($workingWeek->isDirty()) $workingWeek->replicate()->save();

        return back()->with("success", "Mitarbeiter erfolgreich aktualisiert.");
    }
}
