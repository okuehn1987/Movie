<?php

namespace App\Models;

use App\Models\Traits\HasDuration;
use App\Models\Traits\HasLog;
use App\Models\Traits\IsAccountable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLogPatch extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasLog, IsAccountable, HasDuration;

    protected $guarded = [];

    protected $casts = ['is_home_office' => 'boolean'];

    private static function getLogModel()
    {
        return WorkLog::class;
    }

    public static function boot()
    {
        parent::boot();
        self::saving(function (WorkLogPatch $model) {
            Shift::computeAffected($model);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
