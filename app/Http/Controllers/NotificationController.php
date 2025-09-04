<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function update(DatabaseNotification $notification)
    {
        if ($notification->notifiable_id !== Auth::id()) abort(404);
        $notification->markAsRead();

        return back();
    }
}
