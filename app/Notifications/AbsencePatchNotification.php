<?php

namespace App\Notifications;

use App\Models\AbsencePatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AbsencePatchNotification extends Notification
{
    use Queueable;

    protected $user, $absencePatch;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, AbsencePatch $absencePatch)
    {
        $this->user = $user;
        $this->absencePatch = $absencePatch;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return [
            //
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->user->name . ' hat eine Abwesenheitskorrektur beantragt.',
            'absence_patch_id' => $this->absencePatch->id,
            'status' => 'created',
        ];
    }
}
