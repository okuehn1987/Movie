<?php

namespace App\Models;

use App\Models\Traits\HasPatches;
use App\Models\Traits\IsAccountable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Absence extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, HasPatches, IsAccountable;

    protected $guarded = [];

    private static function getPatchModel()
    {
        return AbsencePatch::class;
    }

    public static function boot()
    {
        parent::boot();
        self::saving(function (Absence $model) {
            Shift::computeAffected($model);
        });
    }


    public function getHidden()
    {
        $user = User::find(Auth::id());
        if ($user && $user->cannot('viewShow', [AbsenceType::class, $this->user])) return ['absence_type_id', 'absenceType'];
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
        $usedDays = 0;
        for ($day = Carbon::parse($this->start)->startOfDay(); $day->lte(Carbon::parse($this->end)); $day->addDay()) {
            $currentWorkingWeek = $this->user->userWorkingWeekForDate($day);
            $workingDaysInWeek = $currentWorkingWeek?->numberOfWorkingDays;

            if (
                $workingDaysInWeek > 0 &&
                $currentWorkingWeek->hasWorkDay($day) &&
                !$this->user->operatingSite->hasHoliday($day)
            ) {
                $usedDays++;
            };
        }

        return $usedDays;
    }

    // public function accountAsTransaction()
    // {
    //     DB::transaction(function () {
    //         Shift::lockFor($this->user);
    //         // check all days of absence until today
    //         $end = min(now(), $this->end);

    //         for ($day = Carbon::parse($this->start)->startOfDay(); $day->lte($end); $day->addDay()) {
    //             if (!WorkingHoursCalculation::whereDate('day', $day)->exists()) continue;
    //             if ($this->user->hasAbsenceForDate($day)) continue;

    //             $sollSekunden = $this->user->getSollsekundenForDate($day);

    //             $istSekunden =  $this->user->getWorkDurationForDate($day);
    //             $missingBreakDurationOfShifts = $this->user->shifts()->whereDate('end', $day)->get()->map->missingBreakDuration()->sum();

    //             //6h 15m 1h => -15m -30m => +15m 30m(7:30 - 7)
    //             //6:05 15m 1h => -15m -25m => +15m -5m +30m(7:30 - (7:05-5m))
    //             //5:55 15m 1h => -15m -35m => +15m -0m +35m(7:30 - (6:55 - 0m))
    //             //8h => -30m + 30m => +15m
    //             //9:15 15m 1h => -30m +2:45 => +15m

    //             $this->user->defaultTimeAccount->addBalance(
    //                 max($sollSekunden - $istSekunden, 0),
    //                 'Abwesenheit akzeptiert am ' . Carbon::parse($this->accepted_at)->format('d.m.Y H:i:s')
    //             );
    //         }
    //         $this->save();
    //     });
    // }
}
