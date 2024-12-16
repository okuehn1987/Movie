<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Absence extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $guarded = [];

    protected $hidden = ['absence_type_id', 'absenceType'];

    public function toArray()
    {
        $user = User::find(Auth::id());
        if ($user->can('viewShow', [AbsenceType::class, $this->user])) $this->makeVisible(['absence_type_id', 'absenceType']);
        return parent::toArray();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function absenceType()
    {
        return $this->belongsTo(AbsenceType::class);
    }
}
