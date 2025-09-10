<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Organization;
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
            'customers' => Organization::getCurrent()->customers()->with(['customerOperatingSites.currentAddress'])->get(),
        ]);
    }

    public function show(Customer $customer)
    {
        Gate::authorize('publicAuth', User::class);

        return Inertia::render('Customer/CustomerShow', [
            'customer' => $customer,
            'operatingSites' => $customer->customerOperatingSites()->with('currentAddress')->get(),
            'customerNotes' => $customer->customerNotes,
        ]);
    }

    public function store()
    {
        Gate::authorize('publicAuth', User::class);
        dd(5);
    }

    public function update()
    {
        Gate::authorize('publicAuth', User::class);
        dd(5);
    }
}
