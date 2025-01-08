<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;
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

        if($request->user()->role == 'super-admin') return $next($request);

        foreach ($request->route()->parameters as $key => $instanceObject) {
            $targetOrgId = match (true) {
                $instanceObject instanceof \App\Models\OperatingSite, $instanceObject instanceof \App\Models\AbsenceType,
                $instanceObject instanceof \App\Models\Group, $instanceObject instanceof \App\Models\SpecialWorkingHoursFactor,
                $instanceObject instanceof \App\Models\TimeAccountSetting, => $instanceObject->organization_id,
                $instanceObject instanceof \App\Models\Absence => $instanceObject->absenceType()->select('organization_id')->first()->organization_id,
                $instanceObject instanceof \App\Models\TimeAccount => $instanceObject->timeAccountSetting()->select('organization_id')->first()->organization_id,
                $instanceObject instanceof \App\Models\OperatingTime, $instanceObject instanceof \App\Models\User =>  $instanceObject->operatingSite()->select('organization_id')->first()->organization_id,
                $instanceObject instanceof \App\Models\TravelLog,  $instanceObject instanceof \App\Models\WorkLog, $instanceObject instanceof \App\Models\WorkLogPatch => $instanceObject->user->operatingSite()->select('organization_id')->first()->organization_id,
                $instanceObject instanceof Organization => $instanceObject->id,
                $instanceObject instanceof DatabaseNotification => \App\Models\User::find($instanceObject->notifiable_id)->operatingSite()->select('organization_id')->first()->organization_id,
                default => null
            };
            if ($targetOrgId != $organization->id) {
                if (app()->environment('local')) {
                    throw new class('Could not resolve route paramter: "' . $key . '" with value : "' . $instanceObject . '"') extends Exception implements ProvidesSolution
                    {
                        public function getSolution(): Solution
                        {
                            return BaseSolution::create()
                                ->setSolutionDescription("Did you forget to add the id to the match statement or to typehint the controller method parameter?");
                        }
                    };
                } else abort(404);
            }
        }
        return $next($request);
    }
}
