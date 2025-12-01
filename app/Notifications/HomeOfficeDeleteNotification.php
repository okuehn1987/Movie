<?php

namespace App\Notifications;

use App\Enums\Status;
use App\Models\HomeOfficeDay;
use App\Models\HomeOfficeDayGenerator;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class HomeOfficeDeleteNotification extends Notification
{
    use Queueable;

    protected  $home_office_day_id, $type, $user, $date, $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, HomeOfficeDay $home_office_day, string $date)
    {
        $this->user = $user;
        $this->home_office_day_id = $home_office_day->id;
        $this->date = $date;
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
            ->subject('Herta Nutzerabwesenheitslöschung')
            ->line('für den Nutzer "' . $this->user->name . '" liegt ein Antrag auf Löschung von Homeoffice für den "' .
                Carbon::parse($this->date)->format('d.m.Y') . '" vor. ')
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
            'title' => $this->user->name . ' hat die Löschung von Homeoffice beantragt.',
            'home_office_day_id' => $this->home_office_day_id,
            'status' => Status::Created,
            'triggered_by' => Auth::id(),
        ];
    }

    public function getNotificationURL()
    {
        return  route('dispute.index', [
            'openHomeOfficeDelete' =>  $this->home_office_day_id,
        ]);
    }
}
