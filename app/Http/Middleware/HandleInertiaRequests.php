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
            'ziggy' => (fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ]),
          
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
