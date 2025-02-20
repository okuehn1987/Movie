<?php

namespace App\Models;

use App\Models\Traits\HasPatches;
use App\Models\Traits\IsAccountable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLog extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasPatches, IsAccountable;

    protected $guarded = [];

    protected $casts = ['is_home_office' => 'boolean'];

    private static function getPatchModel()
    {
        return WorkLogPatch::class;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function getDurationAttribute(): int | float
    {
        if ($this->end == null) return 0;
        return Carbon::parse($this->start)->diffInSeconds($this->end);
    }
}
