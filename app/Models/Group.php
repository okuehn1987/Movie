<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $fillable = [
        'name',
        'organization_id'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function groupUsers()
    {
        return $this->hasMany(GroupUser::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
