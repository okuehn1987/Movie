<?php

namespace App\Models;

use App\Addressable;
use Illuminate\Database\Eloquent\Model;

class TicketRecord extends Model
{
    use Addressable;

    protected $guarded = [];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function files()
    {
        return $this->hasMany(TicketRecordFile::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
