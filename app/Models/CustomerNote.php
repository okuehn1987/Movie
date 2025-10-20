<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNote extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function parent()
    {
        return $this->belongsTo(CustomerNote::class, 'parent_id');
    }

    public function subNotes()
    {
        return $this->hasMany(CustomerNote::class, 'parent_id');
    }
}
