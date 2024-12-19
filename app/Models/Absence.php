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

    public function getHidden()
    {
        $user = User::find(Auth::id());
        if ($user->cannot('viewShow', [AbsenceType::class, $this->user])) return ['absence_type_id', 'absenceType'];
        return [];
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
