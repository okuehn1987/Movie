<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\OperatingSite;
use App\Models\User;
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
            'users' => User::inOrganization()->with('group:id,name')->get(),
            'permissions' => User::$PERMISSIONS,
            'groups' => Group::inOrganization()->get(),
            'operating_sites' => OperatingSite::inOrganization()->get()
        ]);
    }

    public function show(User $user)
    {
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
            "password" => "required|string",
            "group_id" => "nullable|integer",
            'operating_site_id' => 'required|integer',
            'permissions' => 'nullable|array',
            'permissions.*' => ['required', Rule::in(collect(User::$PERMISSIONS)->map(fn($p) => $p['name']))]
        ]);

        $user = new User([...$validated, 'date_of_birth' => Carbon::parse($validated['date_of_birth'])]);
        $user->password = Hash::make($validated['password']);

        foreach ($validated['permissions'] as $permission) {
            $user[$permission] = true;
        }

        $user->save();
    }

    public function destroy(User $user)
    {
        $user->delete();
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
            'operating_site_id' => 'required|integer'
        ]);
        $user->update([...$validated, 'date_of_birth' => Carbon::parse(Carbon::parse($validated['date_of_birth'])->format('d-m-Y'))]);

        return back();
    }
}
