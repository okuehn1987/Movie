<?php

namespace App\Http\Controllers;

use App\Models\TimeAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TimeAccountController extends Controller
{
    public function index()
    {
        return Inertia::render('TimeAccount/TimeAccountIndex', [
            'users' => User::find(Auth::id())->group->users()->with(['timeAccounts:id,user_id,balance,balance_limit,type'])->get(['id', 'first_name', 'last_name'])
        ]);
    }
}
