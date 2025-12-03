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
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public static $tideNotificationTypes = [
        AbsenceNotification::class,
        AbsenceDeleteNotification::class,
        AbsencePatchNotification::class,
        DisputeStatusNotification::class,
        WorkLogNotification::class,
        WorkLogPatchNotification::class
    ];

    public static $flowNotificationTypes = [
        TicketFinishNotification::class,
        TicketUpdateNotification::class,
        TicketCreationNotification::class,
        TicketDeletionNotification::class,
        TicketRecordCreationNotification::class,
        RemovedFromTicketNotification::class
    ];

    public function index(#[CurrentUser] User $authUser)
    {

        $archiveNotifications = $authUser->notifications()->paginate(10);
        $flowNotifications = $authUser->unreadNotifications()->whereIn('type', self::$flowNotificationTypes)->paginate(10);
        $tideNotifications = $authUser->unreadNotifications()->whereIn('type', self::$tideNotificationTypes)->paginate(10);

        $userIds = $archiveNotifications->pluck('data.triggered_by')
            ->merge($flowNotifications->pluck('data.triggered_by'))
            ->merge($tideNotifications->pluck('data.triggered_by'))
            ->unique()
            ->values();

        $triggeredByUsers = User::inOrganization()->whereIn('id', $userIds)->get()->each->append('name');

        return Inertia::render('Notification/NotificationIndex', [
            'archiveNotifications' => $archiveNotifications,
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

    public function readAll(Request $request, #[CurrentUser] User $authUser)
    {
        $validated = $request->validate([
            'tab' => 'required|string|in:tide,flow',
        ]);

        (match ($validated['tab']) {
            'tide' => $authUser->unreadNotifications()->whereIn('type', self::$tideNotificationTypes)->get(),
            'flow' => $authUser->unreadNotifications()->whereIn('type', self::$flowNotificationTypes)->get(),
        })->markAsRead();

        return back();
    }
}
