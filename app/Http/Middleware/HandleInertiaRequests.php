<?php

namespace App\Http\Middleware;

use App\Models\ChatAssistant;
use App\Models\ChatMessage;
use App\Models\Customer;
use App\Models\Group;
use App\Models\OperatingSite;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkLog;
use App\Services\AppModuleService;
use App\Services\OpenAIService;
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

    public function shareHerta(Request $request): array
    {
        return [
            'appGlobalCan' => [
                'workLog' => [
                    'viewIndex' => Gate::allows('viewIndex', WorkLog::class),
                ],
            ]
        ];
    }

    public function shareTimesheets(Request $request): array
    {
        return [
            'appGlobalCan' => [
                'customer' => [
                    'viewIndex' => Gate::allows('viewIndex', Customer::class),
                ],
            ]
        ];
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $accessableModules = collect(AppModuleService::getAppModules())
            ->filter(fn($m) => AppModuleService::hasAppModule($m['value']))
            ->values();
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
            'reachedMonthlyTokenLimit' =>
            Organization::getCurrent()->isa_active && Organization::getCurrent()->chatAssistant ?
                OpenAIService::hasReachedTokenLimit(Organization::getCurrent()->chatAssistant) : false,
            'currentUserChat' =>  Organization::getCurrent()->isa_active ?
                $request->user()?->chats()->with('chatMessages:id,chat_id,role,assistant_api_message_id,created_at,msg')->first() : null,
            'currentAppModule' => AppModuleService::currentAppModule(),
            'appModules' => $accessableModules,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'globalCan' => [
                'app' => [
                    'tide' => $accessableModules->pluck('value')->contains('tide'),
                    'flow' => $accessableModules->pluck('value')->contains('flow'),
                ],
                'absence' => [
                    'viewIndex' => Gate::allows('publicAuth', User::class),
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
                    'viewIndex' =>  Gate::allows('viewDisputes', User::class),
                ],
                'chatAssistant' => [
                    'viewIndex' => Gate::allows('viewIndex', ChatAssistant::class),
                ],
            ],
            ...match (AppModuleService::currentAppModule()) {
                'tide' => $this->shareHerta($request),
                'flow' => $this->shareTimesheets($request),
                default => $this->shareHerta($request),
            }
        ];
    }
}
