<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AbsenceDeleteNotification;
use App\Notifications\AbsenceNotification;
use App\Notifications\AbsencePatchNotification;
use App\Notifications\DisputeStatusNotification;
use App\Notifications\RemovedFromTicketNotification;
use App\Notifications\TicketCreationNotification;
use App\Notifications\TicketDeletionNotification;
use App\Notifications\TicketFinishNotification;
use App\Notifications\TicketRecordCreationNotification;
use App\Notifications\TicketUpdateNotification;
use App\Notifications\WorkLogNotification;
use App\Notifications\WorkLogPatchNotification;
use Carbon\Carbon;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, #[CurrentUser] User $authUser)
    {
        $flowNotificationTypes = [
            AbsenceNotification::class,
            AbsenceDeleteNotification::class,
            AbsencePatchNotification::class,
            DisputeStatusNotification::class,
            WorkLogNotification::class,
            WorkLogPatchNotification::class
        ];
        $tideNotificationTypes = [
            TicketFinishNotification::class,
            TicketUpdateNotification::class,
            TicketCreationNotification::class,
            TicketDeletionNotification::class,
            TicketRecordCreationNotification::class,
            RemovedFromTicketNotification::class
        ];

        $notifications = $authUser->notifications()->paginate(10);
        $flowNotifications = $authUser->unreadNotifications()->whereIn('type', $flowNotificationTypes)->paginate(10);
        $tideNotifications = $authUser->unreadNotifications()->whereIn('type', $tideNotificationTypes)->paginate(10);
        $triggeredByUsers = User::inOrganization()->whereIn('id', $notifications->pluck('data.triggered_by'))->get()->each->append('name');

        return Inertia::render('Notification/NotificationIndex', [
            'archiveNotifications' => $notifications,
            'flowNotifications' => $flowNotifications,
            'tideNotifications' => $tideNotifications,
            'triggeredByUsers' => $triggeredByUsers,
        ]);
    }

    public function update(DatabaseNotification $notification)
    {
        if ($notification->notifiable_id !== Auth::id()) abort(404);
        $notification->markAsRead();

        return back();
    }
}
