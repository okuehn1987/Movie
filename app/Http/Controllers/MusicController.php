<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MusicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Music/Index', ['music' => Music::get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        $validated = $request->validate([
            'artist' => 'required|string|max:255',
            'album' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:9999'
        ]);

        $authUser->music()->create($validated);

        return redirect(route('music.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(music $music)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(music $music)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, music $music)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(music $music)
    {
        //
    }
}
