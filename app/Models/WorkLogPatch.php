<?php

namespace App\Models;

use App\FloorToMinutes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class WorkLogPatch extends Model
{
    use HasFactory, SoftDeletes;
    use ScopeInOrganization, FloorToMinutes;

    protected $guarded = [];

    protected $casts = ['is_home_office' => 'boolean'];

    public function workLog()
    {
        return $this->belongsTo(WorkLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationAttribute(): int | float
    {
        return Carbon::parse($this->start)->floatDiffInHours($this->end);
    }

    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => Carbon::now()
        ]);

        if (WorkingHoursCalculation::whereDate('day', $this->workLog->start)->exists()) {
            $this->accountAsTransaction();
        }
    }

    public function accountAsTransaction()
    {
        //TODO: add SWHF's & Nachtschicht, etc.
        DB::transaction(function () {
            $workLog = WorkLog::whereId($this->work_log_id)->lockForUpdate()->first();
            $patch = WorkLogPatch::whereId($this->id)->first();
            if ($patch->is_accounted || !$patch->status == 'accepted') return;

            $oldDuration = $workLog->fresh('currentAccountedPatch')->currentAccountedPatch?->duration ?? $workLog->duration;

            $patch
                ->workLog
                ->user
                ->defaultTimeAccount
                ->addBalance(
                    $patch->getDurationAttribute() - $oldDuration,
                    'Korrektur akzeptiert am ' . Carbon::parse($patch->accepted_at)->format('d.m.Y H:i:s')
                );

            $patch['is_accounted'] = true;
            $patch->save();
        });
    }
}
