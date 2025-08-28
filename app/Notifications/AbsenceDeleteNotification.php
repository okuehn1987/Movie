<?php

namespace App\Notifications;

use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\User;
use Illuminate\Bus\Queueable;
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $buttonText = 'Passwort Zurücksetzen';
        return (new MailMessage)
            ->line('für Ihr Konto ist eine Anfrage zum Löschen einer Abwesenheit bei uns eingegangen.')
            ->line('Um zu Ihrer Anfrage zu gelangen, klicken Sie bitte auf "' . $buttonText . '".')
            ->action($buttonText, $this->url)
            ->line('Aus Sicherheitsgründen ist dieser Link nur 60 Minuten gültig.')
            ->line('Wenn Sie diese Anfrage nicht gestellt haben sollten, können Sie diese Email ignorieren.');
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
}
