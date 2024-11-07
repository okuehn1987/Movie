<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function index()
    {
        return Inertia::render('Group/GroupIndex', [
            'groups' => Group::inOrganization()->select('id', 'name')->paginate(12),
            'users' => User::inOrganization()->select('id', 'first_name', 'last_name', 'email', 'staff_number', 'date_of_birth', 'group_id')->get()
        ]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'users' => 'nullable|array',
            'users.*' => ['required', 'exists:users,id', Rule::in(User::inOrganization()->select('id')->get()->pluck('id'))]
        ]);

        $group = Group::create(['organization_id' => Organization::getCurrent()->id, 'name' => $validated['name']]);
        User::inOrganization()->whereIn('id', $validated['users'])->update(['group_id' => $group->id]);

        return back()->with('success', 'Gruppe erfolgreich erstellt.');
    }
}
