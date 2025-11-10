<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(#[CurrentUser] User $authUser)
    {
        $notifications = $authUser->notifications()->paginate(25);
        $triggeredByUsers = User::inOrganization()->whereIn('id', $notifications->pluck('data.triggered_by'))->get()->each->append('name');

        return Inertia::render('Notification/NotificationIndex', ['notifications' => $notifications, 'triggeredByUsers' => $triggeredByUsers]);
    }

    public function update(DatabaseNotification $notification)
    {
        if ($notification->notifiable_id !== Auth::id()) abort(404);
        $notification->markAsRead();

        return back();
    }
}
