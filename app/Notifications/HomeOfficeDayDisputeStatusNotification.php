<?php

namespace App\Notifications;

use App\Enums\Status;
use App\Models\HomeOfficeDay;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HomeOfficeDayDisputeStatusNotification extends Notification
{
    use Queueable;

    protected  $user, $status, $type, $url, $log;

    /**
     * Create a new notification instance.
     */
    public function __construct(Model $log, User $user, Status $status, string $type = 'create')
    {
        $this->user = $user;
        $this->status = $status;
        $this->type = $type;
        $this->log = $log;
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

        $modelUserNotificationText = match (true) {
            $this->log instanceof HomeOfficeDay => 'auf Homeoffice',
        };

        if ($this->status === Status::Declined) $message
            ->line('Dein Antrag ' . $modelUserNotificationText . ' für den Zeitraum vom "' .
                Carbon::parse($this->log->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->log->end)->format('d.m.Y') . '" wurde abgelehnt.')
            ->action($buttonText, $this->getNotificationURL())
            ->line('Um ihn zu öffnen, klicke bitte auf "' . $buttonText . '".');
        else if ($this->type === 'delete')  $message
            ->line('Dein Antrag auf Löschung einer Abwesenheit für den Zeitraum vom "' .
                Carbon::parse($this->log->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->log->end)->format('d.m.Y') . '" wurde akzeptiert.');
        else  $message
            ->line('Dein Antrag ' . $modelUserNotificationText . ' für den Zeitraum vom "' .
                Carbon::parse($this->log->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->log->end)->format('d.m.Y') . '" wurde akzeptiert.')
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
        $modelClass  = match (true) {
            $this->log instanceof HomeOfficeDay
            => HomeOfficeDay::class,
            default => throw new Exception('Invalid model type for dispute notification.'),
        };

        $modelText = match ($modelClass) {
            HomeOfficeDay::class => 'einer Homeoffice-Zeit',
        };


        $text  = match ($this->type) {
            'delete' => 'Ein Antrag auf Löschung ' . $modelText . ' von dir wurde',
            'create' => 'Ein Antrag  ' . $modelText . ' von dir wurde'
        };

        return [
            'title' => $text . ' ' . ($this->status === Status::Accepted ? 'akzeptiert.' : ($this->status == Status::Created ? 'beantragt.' : 'abgelehnt.')),
            'log_id' => $this->log->id,
            'log_model' => $modelClass,
            'type' => $this->type
        ];
    }

    public function getNotificationURL()
    {
        return match (true) {
            $this->log instanceof HomeOfficeDay =>
            route('absence.index', [
                'openAbsence' => $this->log->id,
            ]),
        };

        return null;
    }
}
