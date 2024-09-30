<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'lastWorkLog' => WorkLog::select('id', 'start', 'end', 'is_home_office')->inOrganization()->where('user_id', Auth::id())->latest('start')->first(),
            'supervisor' => User::select('id', 'first_name', 'last_name')->find(Auth::user()->supervisor_id)
        ]);
    }
}
