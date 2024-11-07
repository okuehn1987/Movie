<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WorkLogController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:1970|max:2099',
            'month' => 'nullable|numeric|min:1|max:12'
        ]);
        $date = Carbon::now();
        if (array_key_exists('year', $validated) && $validated['year'] && array_key_exists('month', $validated) && $validated['month'])
            $date = Carbon::parse($validated['year'] . '-' . $validated['month'] . '-01');

        $users = User::select(['id', 'first_name', 'last_name', 'group_id'])->inOrganization()
            ->whereHas(
                'workLogs',
                fn($q) => $q->whereYear('start', $date->year)->whereMonth('start', $date->month)
            )
            ->with('workLogs:id,start,end,is_home_office,user_id')
            ->get();

        return Inertia::render('WorkLog/WorkLogIndex', [
            'users' => $users,
            'date' => $date,
            'groups' => Group::select(['id', 'name'])->inOrganization()->with('users:id,group_id')->get()
        ]);
    }

    public function store(Request $request)
    {
        $last = (WorkLog::inOrganization()->where('user_id', Auth::id())->latest('start')->first());

        $validated = $request->validate([
            'is_home_office' => 'required|boolean',
            'id' => [
                'nullable',
                Rule::in([$last->id])
            ]
        ]);


        WorkLog::updateOrCreate(['id' => $validated['id']], [
            ...$validated,
            'start' => $validated['id'] ? $last->start :  Carbon::now(),
            'end' => $validated['id'] ? Carbon::now() : null,
            'user_id' => Auth::id()
        ]);

        return back()->with('success', 'Arbeitsstatus erfolgreich eingetragen.');
    }
    public function userWorkLogs(Request $request, User $user)
    {
        return Inertia::render('WorkLog/UserWorkLogIndex', [
            'user' => $user,
            'workLogs' => WorkLog::where('user_id', $user->id)
                ->with('workLogPatches:id,work_log_id,updated_at,status,start,end,is_home_office')
                ->orderBy('start', 'DESC')
                ->paginate(12),
        ]);
    }
}
