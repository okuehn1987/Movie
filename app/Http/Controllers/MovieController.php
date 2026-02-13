<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Auth\Access\Gate;
use Illuminate\Container\Attributes\CurrentUser;
use FFMpeg\FFProbe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;



class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Movies/Index', ['movies' => Movie::when(!Auth::user()->is_admin, fn($q) => $q->where('hidden', false))->get()]);
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
            'rating' => 'string|min:0|max:5',
            'hidden' => 'boolean',
            'movie_file' => 'required',
            'thumbnail_file' => 'required',
            'description' => 'required|string|max:1000'



        ]);

        $validated['movie_file'];
        $movieName = $validated['title'] . '.mp4';
        $thumbnailName = $validated['title'] . '.jpg';
        $moviePath = Storage::disk('movies')->putFileAs('', $validated['movie_file'], $movieName);
        $thumbnailPath = Storage::disk('movies')->putFileAs('thumbnails', $validated['thumbnail_file'], $thumbnailName);





        $authUser->movies()->create([
            'title' => $validated['title'],
            'genre' => $validated['genre'],
            'actor' => $validated['actor'],
            'publicationDate' => $validated['publicationDate'],
            'rating' => $validated['rating'],
            'hidden' => $validated['hidden'],
            'movie_file_path' => $moviePath,
            'thumbnail_file_path' => $thumbnailPath,
            'description' => $validated['description'],
            'duration_in_seconds' => FFProbe::create()->format(Storage::disk('movies')->path($moviePath))->get('duration'),
        ]);


        return back(); //redirect(route('movies.index'));
    }


    public function getMovieContent(Movie $movieFile,)
    {
        return response()->file(Storage::disk('movies')->path($movieFile->movie_file_path));
    }

    public function getThumbnailContent(Movie $thumbnailFile)
    {
        return response()->file(Storage::disk('movies')->path($thumbnailFile->thumbnail_file_path));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Movie $movie)
    {
        // dd($movie);
        return Inertia::render('Movies/Show', ['movie' => $movie]);
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
