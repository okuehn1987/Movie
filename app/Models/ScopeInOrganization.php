<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

trait ScopeInOrganization
{
    public static function scopeInOrganization(Builder $builder)
    {
        $org = Organization::getCurrent();
        if (new self instanceof \App\Models\Abscence) {
            return $builder->whereIn('abscence_type_id', AbscenceType::select('id')->inOrganization());
        }
        if (new self instanceof \App\Models\OperatingTime || new self instanceof \App\Models\User) {
            return  $builder->whereIn(
                'operating_site_id',
                OperatingSite::select('id')->inOrganization()
            );
        }
        if (new self instanceof \App\Models\TravelLog || new self instanceof \App\Models\WorkLog) {
            return $builder->whereIn('user_id', User::select('id')->inOrganization());
        }
        if (
            new self instanceof \App\Models\OperatingSite || new self instanceof \App\Models\AbscenceType ||
            new self instanceof \App\Models\Group ||  new self instanceof \App\Models\SpecialWorkingHoursFactor
        ) {
            return $builder->where('organization_id', $org->id);
        }
        abort(501);
    }
}
