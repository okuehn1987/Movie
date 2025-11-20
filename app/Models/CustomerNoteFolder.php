<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNoteFolder extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function entries()
    {
        return $this->hasMany(CustomerNoteEntry::class);
    }

    public function subFolders()
    {
        return $this->hasMany(CustomerNoteFolder::class);
    }
}
