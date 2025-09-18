<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreationNotification extends Notification
{
    use Queueable;
    protected $user, $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Ticket $ticket)
    {
        $this->user = $user;
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
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->user->name . ' hat ein neues Ticket erstellt.',
            'ticket_id' => $this->ticket->id,
        ];
    }

    public function getNotificationURL()
    {
        return  route('ticket.index', [
            'openTicket' => $this->ticket->id,
        ]);
    }
}
