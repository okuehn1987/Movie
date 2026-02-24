<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Movie;
use App\Models\User;
use FontLib\Table\Type\name;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Comment/Index');
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
    public function store(Request $request, #[CurrentUser] User $authUser, Movie $movie)
    {


        $validated = $request->validate([
            'comment' => 'required|string|max:1000',

        ]);

        $authUser->comments()->create([
            'comment' => $validated['comment'],
            'movie_id' => $movie->id,
            'name' => $authUser->name
        ]);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        //
    }
}
