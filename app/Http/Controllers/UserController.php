<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('User/UserIndex', ['users' => User::inOrganization()->get()]);
    }

    public function show(User $user)
    {
        return Inertia::render('User/UserShow', ['user' => $user]);
    }
}
