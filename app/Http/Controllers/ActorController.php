<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;



class ActorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Actor/Index', ['actor' => Actor::when(!Auth::user()->is_admin, fn($q) => $q->where('hidden', false))->get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Actor/Create', ['actor' => Actor::when(!Auth::user()->is_admin, fn($q) => $q->where('hidden', false))->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        Actor::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name']
        ]);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
