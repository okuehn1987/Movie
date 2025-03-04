<?php

namespace App\Models;

use App\Models\Traits\HasDuration;
use App\Models\Traits\HasPatches;
use App\Models\Traits\IsAccountable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLog extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasPatches, IsAccountable, HasDuration;

    private static function getPatchModel()
    {
        return WorkLogPatch::class;
    }

    protected $guarded = [];

    protected $casts = ['is_home_office' => 'boolean'];

    public static function boot()
    {
        parent::boot();
        self::saving(function (WorkLog $model) {
            Shift::computeAffected($model);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
