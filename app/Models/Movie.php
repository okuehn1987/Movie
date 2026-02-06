<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'genre',
        'actor',
        'publicationDate',
        'movieLength',
        'rating',
        'hidden',
        'movie_file_path',
        'thumbnail_file_path'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
