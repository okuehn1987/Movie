<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        Gate::authorize('publicAuth', User::class);

        return Inertia::render('Customer/CustomerIndex', [
            'customers' => Customer::with(['currentAddress'])->get(),
        ]);
    }

    public function store()
    {
        Gate::authorize('publicAuth', User::class);
    }

    public function update()
    {
        Gate::authorize('publicAuth', User::class);
    }
}
