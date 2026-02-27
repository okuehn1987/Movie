<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'genre',
        'publication_date',
        'movieLength',
        'rating',
        'hidden',
        'movie_file_path',
        'thumbnail_file_path',
        'duration_in_seconds',
        'description',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function actors()
    {
        return $this->belongsToMany(Actor::class);
    }
}
