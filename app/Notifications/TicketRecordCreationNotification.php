<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\User;
use App\Models\TicketRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRecordCreationNotification extends Notification
{
    use Queueable;
    protected $user, $ticketRecord;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, TicketRecord $ticketRecord)
    {
        $this->user = $user;
        $this->ticketRecord = $ticketRecord;
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
            'title' => $this->user->name . ' hat einen neuen Eintrag zu einem Ticket erstellt.',
            'ticket_id' => $this->ticketRecord->id,
            'status' => 'created',
        ];
    }

    public function getNotificationURL()
    {
        return  route('dispute.index', [
            'openTicket' => $this->ticketRecord->ticket->id,
        ]);
    }
}
