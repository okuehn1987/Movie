<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemovedFromTicketNotification extends Notification
{
    use Queueable;

    private $authUser, $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $authUser, Ticket $ticket)
    {
        $this->authUser = $authUser;
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->notification_channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Timesheets - (" . $this->ticket->referenceNumber . ")")
            ->line('Du wurdest vom Ticket ' . $this->ticket->referenceNumber . ' entfernt.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Du wurdest vom Ticket ' . $this->ticket->referenceNumber . ' entfernt',
        ];
    }
}
