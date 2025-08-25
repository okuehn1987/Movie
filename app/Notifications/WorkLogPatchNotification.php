<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\WorkLogPatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkLogPatchNotification extends Notification
{
    use Queueable;

    protected $user, $patch;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, WorkLogPatch $patch)
    {
        $this->user = $user;
        $this->patch = $patch;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        //
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->user->name . ' hat eine Zeitkorrektur beantragt.',
            'work_log_patch_id' => $this->patch->id,
            'status' => 'created',
        ];
    }
}
