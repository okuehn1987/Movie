<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketRecordFile extends Model
{
    protected $guarded = [];

    public function ticketRecord()
    {
        return $this->belongsTo(TicketRecord::class);
    }
}
