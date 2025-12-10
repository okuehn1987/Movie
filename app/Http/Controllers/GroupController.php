<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\OperatingSite;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GroupController extends Controller
{
    public function index()
    {
        Gate::authorize('viewIndex', Group::class);

        return Inertia::render('Group/GroupIndex', [
            'groups' => Group::inOrganization()->select('id', 'name')->with('users:id,first_name,last_name,group_id')->get(),
            'users' => User::inOrganization()->get(['id', 'first_name', 'last_name'])->map(fn($user) => [...$user->toArray(), 'name' => $user->name]),
            'can' => [
                'group' => [
                    'create' => Gate::allows('create', Group::class),
                    'delete' => Gate::allows('delete', Group::class),
                    'update' => Gate::allows('update', Group::class),
                ]
            ]
        ]);
    }

    public function update(Request $request, Group $group)
    {
        Gate::authorize('update', $group);

        $validated = $request->validate([
            'name' => 'required|string',
            'users' => 'present|array',
            'users.*' => ['required', Rule::exists('users', 'id')->whereIn('operating_site_id', OperatingSite::inOrganization()->select('id'))]
        ]);

        $group->update(['name' => $validated['name']]);
        $group->users()->whereNotIn('users.id', $validated['users'])->update(['group_id' => null]);
        User::inOrganization()->whereIn('id', $validated['users'])->update(['group_id' => $group->id]);

        return back()->with('success', 'Abteilung erfolgreich aktualisiert.');
    }

    public function destroy(Group $group)
    {
        Gate::authorize('delete', $group);

        $group->delete();

        return back()->with('success', 'Abteilung erfolgreich gelöscht.');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Group::class);

        $validated = $request->validate([
            'name' => 'required|string',
            'users' => 'present|array',
            'users.*' => ['required', Rule::exists('users', 'id')->whereIn('operating_site_id', OperatingSite::inOrganization()->select('id'))]
        ]);

        $group = Group::create(['organization_id' => Organization::getCurrent()->id, 'name' => $validated['name']]);
        User::inOrganization()->whereIn('id', $validated['users'])->update(['group_id' => $group->id]);

        return back()->with('success', 'Gruppe erfolgreich erstellt.');
    }
}
