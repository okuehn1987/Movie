<?php

namespace App\Notifications;

use App\Enums\Status;
use App\Events\NotificationCreated;
use App\Models\HomeOfficeDayGenerator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class HomeOfficeDayNotification extends Notification
{
    use Queueable;

    protected $start, $end, $home_office_day_generator_id, $type, $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(HomeOfficeDayGenerator $homeOfficeDayGenerator, User $user)
    {
        $this->start = $homeOfficeDayGenerator->start;
        $this->end = $homeOfficeDayGenerator->end;
        $this->home_office_day_generator_id = $homeOfficeDayGenerator->id;
        $this->user = $user;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = $notifiable->notification_channels ?? ['database'];

        if (in_array('database', $channels)) {
            event(new NotificationCreated($notifiable));
        }
        return $notifiable->notification_channels ?? ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        $buttonText = 'Antrag einsehen';

        return (new MailMessage)
            ->subject('Herta Antrag auf Homeoffice')
            ->line('für den Nutzer "' . $this->user->name . '" liegt ein Antrag auf Homeoffice für den Zeitraum vom "' .
                Carbon::parse($this->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->end)->format('d.m.Y') . '" vor.')
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
            'title' => $this->user->name . ' hat Homeoffice beantragt.',
            'home_office_day_generator_id' => $this->home_office_day_generator_id,
            'start' => $this->start,
            'triggered_by' => Auth::id(),
            'status' => Status::Created,
        ];
    }

    public function getNotificationURL()
    {

        return route('dispute.index', [
            'openHomeOfficeDays' => $this->home_office_day_generator_id,
        ]);
    }
}
