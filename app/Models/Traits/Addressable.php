<?php

namespace App\Models\Traits;

use App\Models\Address;

trait Addressable
{
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function currentAddress()
    {
        return $this->addresses()->one()->ofMany(['active_since' => 'max', 'created_at' => 'max'], fn($q) => $q->where('active_since', '<=', now()));
    }
}
