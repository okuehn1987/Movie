<?php

namespace App\Models;

use App\Addressable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerOperatingSite extends Model
{
    use HasFactory, SoftDeletes;
    use Addressable, ScopeInOrganization;

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
