<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function index()
    {
        return Inertia::render('Group/GroupIndex', ['groups' => Group::inOrganization()->with('users')->get(), 'users' => User::inOrganization()->get()->map(fn($u) => ['name' => $u->first_name . ' ' . $u->last_name,  'id' => $u->id])]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'users' => 'array'
        ]);

        $group = Group::create(['organization_id' => Organization::getCurrent()->id, 'name' => $validated['name']]);
        User::inOrganization()->whereIn('id', $validated['users'])->update(['group_id' => $group->id]);

        return back();
    }
}
