<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use ScopeInOrganization;

    protected $guarded = [];

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

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function assign(User $user)
    {
        $this->update([
            "assignee_id" => $user->id,
            "assigned_at" => now(),
        ]);
    }
}
