<?php

namespace App\Models;

use App\Models\Traits\HasDuration;
use App\Models\Traits\HasPatches;
use App\Models\Traits\IsAccountable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelLog extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasPatches, IsAccountable, HasDuration;

    protected $guarded = [];

    private static function getPatchModel()
    {
        return TravelLogPatch::class;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
