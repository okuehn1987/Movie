<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function accountAsTransaction()
    {
        DB::transaction(function () {
            WorkLog::where('user_id', $this->user_id)->lockForUpdate()->get();

            $this->accepted_at = Carbon::now();
            $this->status = 'accepted';

            // check all days of absence until today
            $end = Carbon::parse($this->end)->gte(Carbon::now()) ? Carbon::now() : Carbon::parse($this->end);

            for ($day = Carbon::parse($this->start)->startOfDay(); $day->lte($end); $day->addDay()) {
                if (!WorkingHoursCalculation::whereDate('day', $day)->exists()) continue;

                $hasAppliedAbsenceForDay = $this->user()
                    ->absences()
                    ->where('id', '!=', $this->id)
                    ->where('status', 'accepted')
                    ->whereDate('start', '<=', $day)
                    ->whereDate('end', '>=', $day)
                    ->exists();
                if ($hasAppliedAbsenceForDay) continue;

                $sollStunden = $this->user->getSollstundenForDate($day);

                $istStunden =  WorkLog::whereDate('start', $day)
                    ->where('user_id', $this->user_id)
                    ->get()
                    ->sum('duration');

                $this->user->defaultTimeAccount->addBalance(
                    max($sollStunden - $istStunden, 0),
                    'Abwesenheit akzeptiert am ' . Carbon::parse($this->accepted_at)->format('d.m.Y H:i:s')
                );
            }
            $this->save();
        });
    }
}
