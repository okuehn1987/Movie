<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNoteEntry extends Model
{
    protected $casts = ['metadata' => 'array'];

    protected $guarded = [];

    public function folder()
    {
        return $this->belongsTo(CustomerNoteFolder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }
}
