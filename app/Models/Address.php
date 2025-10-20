<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Address extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static $ADDRESS_KEYS = [
        'street',
        'house_number',
        'address_suffix',
        'zip',
        'city',
        'federal_state',
        'country',
    ];

    public function addressable()
    {
        return $this->morphTo();
    }

    public function ticketRecords()
    {
        return $this->hasMany(TicketRecord::class);
    }

    public function ticket()
    {
        return $this->hasMany(Ticket::class);
    }

    public static function addressablesWithName(MorphTo $morphTo)
    {
        return $morphTo->morphWith([
            User::class => ['id', 'first_name', 'last_name'],
            OperatingSite::class => ['id', 'name'],
            CustomAddress::class => ['id', 'name'],
            CustomerOperatingSite::class => ['id', 'name'],
        ]);
    }
}
