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
}
