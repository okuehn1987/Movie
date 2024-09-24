<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperatingTime extends Model
{
    use HasFactory, SoftDeletes, ScopeInOrganization;

    protected $fillable = ['operating_site_id', 'start', 'end', 'type'];

    public function operatingSite()
    {
        return $this->belongsTo(OperatingSite::class);
    }
}
