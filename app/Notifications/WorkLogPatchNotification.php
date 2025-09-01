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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $buttonText = 'Antrag einsehen';
        return (new MailMessage)
            ->subject('Herta Zeitkorrekturantrag')
            ->line('für einen User liegt ein Zeitkorrekturantrag vor.')
            ->line('Um fortzufahren, klicke bitte auf "' . $buttonText . '".')
            ->action($buttonText,  $this->getNotificationURL());
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

    public function readNotification(array $notification)
    {
        \Illuminate\Notifications\DatabaseNotification::find($notification['id'])?->markAsRead();
    }

    public function getNotificationURL()
    {
        return  route('dispute.index', [
            'openPatch' => $this->patch->id,
        ]);
    }
}
