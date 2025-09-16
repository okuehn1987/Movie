<?php

namespace App\Http\Middleware;

use App\Models\Absence;
use App\Models\Group;
use App\Models\OperatingSite;
use App\Models\Organization;
use App\Models\TimeAccountSetting;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
            ],
            'unreadNotifications' => $request->user()?->unreadNotifications,
            'ziggy' => (fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ]),
            'organization' => Organization::getCurrent(),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'globalCan' => [
                'absence' => [
                    'viewIndex' => Gate::allows('publicAuth', User::class),
                ],
                'workLog' => [
                    'viewIndex' => Gate::allows('viewIndex', WorkLog::class),
                ],
                'organization' => [
                    'viewIndex' =>  Gate::allows('viewIndex', Organization::class),
                    'viewShow' => Gate::allows('viewShow', Organization::class),
                ],
                'operatingSite' => [
                    'viewIndex' => Gate::allows('viewIndex', OperatingSite::class),
                ],
                'group' => [
                    'viewIndex' => Gate::allows('viewIndex', Group::class),
                ],
                'user' => [
                    'viewIndex' => Gate::allows('viewIndex', User::class),
                ],
                'dispute' => [
                    'viewIndex' => !!$request->user()?->is_supervisor,
                ],
            ]
        ];
    }
}
