<?php

namespace App\Http\Middleware;

use App\Models\AbscenceType;
use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasOrganizationAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $organization = Organization::getCurrent();

        foreach ($request->route()->parameters as $instanceObject) {
            dd($instanceObject);
            $targetOrgId = match (true) {
                $instanceObject instanceof \App\Models\OperatingSite, $instanceObject instanceof \App\Models\AbscenceType,
                $instanceObject instanceof \App\Models\Group, $instanceObject instanceof \App\Models\SpecialWorkingHoursFactor => $instanceObject->organization_id,
                $instanceObject instanceof \App\Models\Abscence => $instanceObject->abscenceType()->select('organization_id')->get()->organization_id,
                $instanceObject instanceof \App\Models\OperatingTime, $instanceObject instanceof \App\Models\User =>  $instanceObject->operatingSite()->select('organization_id')->first()->organization_id,
                $instanceObject instanceof \App\Models\TravelLog,  $instanceObject instanceof \App\Models\WorkLog => $instanceObject->user()->operatingSite()->select('organization_id')->organization_id,
            };
            if ($targetOrgId != $organization->id)
                return abort(app()->environment('local') ? 403 : 404);
        }
        return $next($request);
    }
}
