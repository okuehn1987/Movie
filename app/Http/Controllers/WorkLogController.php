<?php

namespace App\Http\Controllers;

use App\Models\WorkLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkLogController extends Controller
{
    public function index()
    {
        return Inertia::render('WorkLog/WorkLogIndex', ['workLogs' => WorkLog::inOrganization()->get()]);
    }
}
