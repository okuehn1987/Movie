<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Inertia\Inertia;


class MoviesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Movies/Index', ['movies' => Movie::get()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Movies/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, #[CurrentUser] User $authUser)
    {
        // dd($request->all());
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'actor' => 'required|string|max:255',
            'publicationDate' => 'required|string|max:255',
            'movieLength' => 'required|string|max:255',
            'rating' => 'string|min:0|max:5',
            'hidden' => 'boolean',
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:1940|max:9999',
        ]);

        $authUser->movies()->create($validated);

        return back(); //redirect(route('movies.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Movie $movie)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movie $movie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movie $movie)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movie $movie)
    {
        //
    }
}
