<?php

namespace App\Models\Traits;

use App\Enums\Status;

trait IsAccountable
{
    public function accept()
    {
        $this->update([
            'status' => Status::Accepted,
            'accepted_at' => now()
        ]);
    }
    public function decline()
    {
        $this->update([
            'status' => Status::Declined,
        ]);
    }
}
