<?php

namespace App\Models\Scopes;

use App\Models\Abscence;
use App\Models\AbscenceType;
use App\Models\Group;
use App\Models\OperatingSite;
use App\Models\OperatingTime;
use App\Models\Organization;
use App\Models\SpecialWorkingHoursFactor;
use App\Models\TravelLog;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InOrganization implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        $org = Organization::getCurrent();

        if ($model instanceof \App\Models\Abscence) {
            return $builder->whereIn('abscence_type_id', AbscenceType::select('id'));
        }
        if ($model instanceof \App\Models\OperatingTime || $model instanceof \App\Models\User) {
            return  $builder->whereIn('operating_site_id', OperatingSite::select('id'));
        }
        if ($model instanceof \App\Models\TravelLog || $model instanceof \App\Models\WorkLog) {
            return $builder->whereIn('user_id', User::select('id'));
        }
        if (
            $model instanceof \App\Models\OperatingSite || $model instanceof \App\Models\AbscenceType ||
            $model instanceof \App\Models\Group ||  $model instanceof \App\Models\SpecialWorkingHoursFactor
        ) {
            return $builder->where('organization_id', $org->id);
        }
        dd($model);
        abort(501);
    }
}
