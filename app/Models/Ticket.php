<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use ScopeInOrganization;

    protected $guarded = [];

    protected $appends = ['reference_number'];

    public function referenceNumber(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->reference_prefix . '-' . $this->id
        );
    }

    public function records()
    {
        return $this->hasMany(TicketRecord::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('status');
    }
}
