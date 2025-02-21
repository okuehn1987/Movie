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
        self::saving(function ($model) {

            if (!$model->shift_id) {
                $shift = Shift::create([
                    'user_id' => $model->user_id,
                    'is_accounted' => false,
                    'start' => $model->start,
                    'end' => $model->end,
                ]);
                $model->update(['shift_id' => $shift->id]);
            }
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
