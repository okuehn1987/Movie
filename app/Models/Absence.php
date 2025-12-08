<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Traits\HasPatches;
use App\Models\Traits\IsAccountable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Absence extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasPatches, IsAccountable;

    protected $guarded = [];

    protected $casts = [
        'status' => Status::class,
    ];

    private static function getPatchModel()
    {
        return AbsencePatch::class;
    }

    public static function boot()
    {
        parent::boot();
        self::saving(function (Absence $model) {
            //TODO: check if we need to split on save
            Shift::computeAffected($model);
        });

        self::deleting(function (Absence $model) {
            $patch = new AbsencePatch([
                'start' => '1970-01-01',
                'end' => '1970-01-01',
                'user_id' => $model->user_id,
                'absence_type_id' => $model->absence_type_id,
                'status' => Status::Accepted,
                'accepted_at' => now(),
                'comment' => $model->comment,
                'type' => 'delete',
                'absence_id' => $model->id,
            ]);

            Shift::computeAffected($patch);
            $patch->saveQuietly();
        });
    }


    public function getHidden()
    {
        $user = request()->user();
        if ($user && $user->cannot('viewShow', [AbsenceType::class, $user->usersInOrganization->find($this->user_id)])) return ['absence_type_id', 'absenceType'];
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

    public function getDurationAttribute()
    {
        return (int)Carbon::parse($this->start)->diffInDays(Carbon::parse($this->end)) + 1;
    }

    public function getUsedDaysAttribute()
    {
        return self::calculateUsedDays($this);
    }

    public static function calculateUsedDays(Absence|AbsencePatch $entry)
    {
        $usedDays  = [];
        for ($day = Carbon::parse($entry->start)->startOfDay(); $day->lte(Carbon::parse($entry->end)); $day->addDay()) {
            $currentWorkingWeek = $entry->user->userWorkingWeekForDate($day);

            if (
                $currentWorkingWeek?->hasWorkDay($day) &&
                !$entry->user->loadMissing('operatingSite')->operatingSite->hasHoliday($day)
            ) {
                $usedDays[$day->year] = ($usedDays[$day->year] ?? 0) + 1;
            };
        }

        return $usedDays;
    }
}
