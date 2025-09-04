<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore(Auth::id()),
            ],
        ]);

        $request->user()->update([...$validated]);

        return back()->with('success', 'Profilinformationen erfolgreich gespeichert.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        Gate::authorize('publicAuth', User::class);

        $validated = $request->validate([
            'mail_notifications' => 'required|boolean',
            'app_notifications' => 'required|boolean',
        ]);

        $notification_channels = [];
        if ($validated['mail_notifications']) $notification_channels[] = 'mail';
        if ($validated['app_notifications']) $notification_channels[] = 'database';
        $request->user()->update(['notification_channels' => $notification_channels]);

        return back()->with('success', 'Einstellungen erfolgreich gespeichert.');
    }
}
