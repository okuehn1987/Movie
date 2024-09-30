<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WorkLogController extends Controller
{
    public function index()
    {
        return Inertia::render('WorkLog/WorkLogIndex', ['workLogs' => WorkLog::inOrganization()->get()]);
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

        return back();
    }
}
