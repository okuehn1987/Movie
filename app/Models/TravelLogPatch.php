<?php

namespace App\Models;

use App\Models\Traits\HasLog;
use App\Models\Traits\IsAccountable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelLogPatch extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasLog, IsAccountable;

    protected $guarded = [];

    private static function getLogModel()
    {
        return TravelLog::class;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
