<?php

namespace App\Notifications;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbsenceDeleteNotification extends Notification
{
    use Queueable;

    protected $user, $absence, $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Absence $absence)
    {
        $this->user = $user;
        $this->absence = $absence;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
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
            ->subject('Herta Löschung Nutzer Abwesenheit')
            ->line('für einen User liegt ein Löschungsantrag vor.')
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
            'title' => $this->user->name . ' hat die Löschung einer Abwesenheit beantragt.',
            'absence_id' => $this->absence->id,
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
            'openAbsenceDelete' => $this->absence->id,
        ]);
    }
}
