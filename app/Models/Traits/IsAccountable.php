<?php

namespace App\Models\Traits;

use App\Models\WorkingHoursCalculation;
use App\Models\WorkLogPatch;
use Carbon\Carbon;

trait IsAccountable
{
    public function accept()
    {
        // FIXME: add all other instances
        if (!$this instanceof WorkLogPatch) {
            throw new \Exception('This method can only be called on a WorkLog instance');
        }

        $this->update([
            'status' => 'accepted',
            'accepted_at' => Carbon::now()
        ]);

        if (WorkingHoursCalculation::whereDate('day', $this->log->start)->exists()) {
            $this->accountAsTransaction();
        }
    }

    // public function accountAsTransaction()
    // {
    //     //TODO: add SWHF's & Nachtschicht, etc.
    //     DB::transaction(function () {
    //         $workLog = WorkLog::whereId($this->work_log_id)->lockForUpdate()->first();
    //         $patch = WorkLogPatch::whereId($this->id)->first();
    //         if ($patch->is_accounted || !$patch->status == 'accepted') return;

    //         $oldDuration = $workLog->fresh('currentAccountedPatch')->currentAccountedPatch?->duration ?? $workLog->duration;

    //         $patch
    //             ->workLog
    //             ->user
    //             ->defaultTimeAccount
    //             ->addBalance(
    //                 $patch->getDurationAttribute() - $oldDuration,
    //                 'Korrektur akzeptiert am ' . Carbon::parse($patch->accepted_at)->format('d.m.Y H:i:s')
    //             );

    //         $patch['is_accounted'] = true;
    //         $patch->save();
    //     });
    // }
}
