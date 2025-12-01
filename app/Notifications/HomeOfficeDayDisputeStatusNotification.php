<?php

namespace App\Notifications;

use App\Enums\Status;
use App\Models\HomeOfficeDay;
use App\Models\HomeOfficeDayGenerator;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class HomeOfficeDayDisputeStatusNotification extends Notification
{
    use Queueable;

    protected $start, $end, $home_office_day_generator_id, $status, $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(HomeOfficeDayGenerator $home_office_day_generator, Status $status, string $type = 'create')
    {
        $this->status = $status;
        $this->type = $type;
        $this->start = $home_office_day_generator->start;
        $this->end = $home_office_day_generator->end;
        $this->home_office_day_generator_id = $home_office_day_generator->id;
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

        $message = (new MailMessage)->subject('Herta Antragsaktualisierung');

        $modelUserNotificationText =  'auf Homeoffice';

        if ($this->status === Status::Declined) $message
            ->line('Dein Antrag ' . $modelUserNotificationText . ' für den Zeitraum vom "' .
                Carbon::parse($this->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->end)->format('d.m.Y') . '" wurde abgelehnt.')
            ->action($buttonText, $this->getNotificationURL())
            ->line('Um ihn zu öffnen, klicke bitte auf "' . $buttonText . '".');
        else if ($this->type === 'delete')  $message
            ->line('Dein Antrag auf Löschung einer Abwesenheit für den Zeitraum vom "' .
                Carbon::parse($this->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->end)->format('d.m.Y') . '" wurde akzeptiert.');
        else  $message
            ->line('Dein Antrag ' . $modelUserNotificationText . ' für den Zeitraum vom "' .
                Carbon::parse($this->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->end)->format('d.m.Y') . '" wurde akzeptiert.')
            ->action($buttonText, $this->getNotificationURL())
            ->line('Um ihn zu öffnen, klicke bitte auf "' . $buttonText . '".');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        $modelText = 'einer Homeoffice-Zeit';


        $text  = match ($this->type) {
            'delete' => 'Ein Antrag auf Löschung ' . $modelText . ' von dir wurde',
            'create' => 'Ein Antrag  ' . $modelText . ' von dir wurde'
        };

        return [
            'title' => $text . ' ' . ($this->status === Status::Accepted ? 'akzeptiert.' : ($this->status == Status::Created ? 'beantragt.' : 'abgelehnt.')),
            'home_office_day_generator_id' => $this->home_office_day_generator_id,
            'start' => $this->start,
            'type' => $this->type,
            'triggered_by' => Auth::id(),
        ];
    }

    public function getNotificationURL()
    {
        return
            route('absence.index', [
                'date' => $this->start,
            ]);
    }
}
