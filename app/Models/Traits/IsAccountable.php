<?php

namespace App\Models\Traits;

trait IsAccountable
{
    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now()
        ]);
    }
    public function decline()
    {
        $this->update([
            'status' => 'declined',
        ]);
    }
}
