<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    protected $user, $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $url, User $user)
    {
        $this->url = $url;
        $this->user = $user;
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
            ->line('für Ihr Konto ist eine Anfrage zum Zurücksetzen des Passworts bei uns eingegangen.')
            ->line('Um fortzufahren, klicken Sie bitte auf "' . $buttonText . '".')
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
            //
        ];
    }
}
