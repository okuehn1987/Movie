<?php

namespace App\Notifications;

use App\Enums\Status;
use App\Models\HomeOfficeDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HomeOfficeDayNotification extends Notification
{
    use Queueable;

    protected $user, $homeOfficeDays;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user,)
    {
        $this->user = $user;
        $this->homeOfficeDays = $homeOfficeDays;
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
    public function toMail(object $notifiable)
    {
        $buttonText = 'Antrag einsehen';

        return (new MailMessage)
            ->subject('Herta Antrag auf Homeoffice')
            ->line('für den Nutzer "' . $this->user->name . '" liegt ein Antrag auf Homeoffice für den Zeitraum vom "' .
                Carbon::parse($this->homeOfficeDay->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->homeOfficeDay->end)->format('d.m.Y') . '" vor.')
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
            'absence_id' => $this->homeOfficeDay->id,
            'status' => Status::Created,
        ];
    }

    public function getNotificationURL()
    {
        return route('dispute.index', [
            'openAbsence' => $this->homeOfficeDay->id,
        ]);
    }
}
