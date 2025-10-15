<?php

namespace App\Models;

use App\Models\Traits\Addressable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomAddress extends Model
{
    use HasFactory, Addressable;

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
