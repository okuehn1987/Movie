<?php

namespace App\Notifications;

use App\Enums\Status;
use App\Models\AbsencePatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return $notifiable->notification_channels ?? ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $buttonText = 'Antrag einsehen';

        return (new MailMessage)
            ->subject('Herta Abwesenheitskorrekturantrag')
            ->line('für den Nutzer "' . $this->user->name . '" liegt ein Antrag auf Korrektur einer Abwesenheit für den Zeitraum vom "' .
                Carbon::parse($this->absencePatch->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->absencePatch->end)->format('d.m.Y') . '" vor.')
            ->line('Um fortzufahren, klicke bitte auf "' . $buttonText . '".')
            ->action($buttonText,  $this->getNotificationURL());
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
            'status' => Status::Created,
        ];
    }

    public function getNotificationURL()
    {
        return route('dispute.index', [
            'openAbsencePatch' => $this->absencePatch->id,
        ]);
    }
}
