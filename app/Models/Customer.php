<?php

namespace App\Models;

use App\Models\Traits\Addressable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Customer extends Model
{
    use HasFactory;
    use Addressable, ScopeInOrganization;

    protected $guarded = [];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function customerOperatingSites()
    {
        return $this->hasMany(CustomerOperatingSite::class);
    }

    public function customerNoteFolders()
    {
        return $this->hasMany(CustomerNoteFolder::class);
    }

    public function customerNoteEntries()
    {
        return $this->hasManyThrough(CustomerNoteEntry::class, CustomerNoteFolder::class);
    }

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }
}
