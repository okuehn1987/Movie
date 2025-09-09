<?php

namespace App\Models;

use App\Addressable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    use Addressable;

    protected $guarded = [];
}
