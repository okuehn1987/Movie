<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\WorkLog;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class WorkLogNotification extends Notification
{
    use Queueable;

    protected $user, $log;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, WorkLog $log)
    {
        $this->user = $user;
        $this->log = $log;
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
        $buttonText = 'Antrag einsehen';
        return (new MailMessage)
            ->subject('Herta Buchungsantrag')
            ->line('für den Nutzer "' . $this->user->name . '" liegt ein Antrag auf eine Buchung für den Zeitraum vom "' .
                Carbon::parse($this->log->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->log->end)->format('d.m.Y') . '" vor.')
            ->line('Um fortzufahren, klicke bitte auf "' . $buttonText . '".')
            ->action($buttonText,  $this->getNotificationURL());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->user->name . ' hat eine neue Buchung beantragt.',
            'work_log_id' => $this->log->id,
            'status' => 'created',
            'triggered_by' => Auth::id(),
        ];
    }

    public function readNotification(array $notification)
    {
        \Illuminate\Notifications\DatabaseNotification::find($notification['id'])?->markAsRead();
    }

    public function getNotificationURL()
    {
        return  route('dispute.index', [
            'openWorkLog' => $this->log->id,
        ]);
    }
}
