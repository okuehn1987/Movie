<?php

namespace App\Notifications;

use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Enums\Status;
use App\Models\TravelLog;
use App\Models\TravelLogPatch;
use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class DisputeStatusNotification extends Notification
{
    use Queueable;

    protected  $log, $status, $type, $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(Model $log, Status $status, string $type = 'create')
    {
        $this->log = $log;
        $this->status = $status;
        $this->type = $type;
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

        $message = (new MailMessage)->subject('Tide Antragsaktualisierung');

        $modelUserNotificationText = match (true) {
            $this->log instanceof WorkLog => 'auf Arbeitszeitbuchung',
            $this->log instanceof WorkLogPatch => 'auf Arbeitszeitkorrektur',
            $this->log instanceof TravelLog => 'auf Dienstreise',
            $this->log instanceof TravelLogPatch => 'auf Dienstreisenkorrektur',
            $this->log instanceof Absence => 'auf Abwesenheit',
            $this->log instanceof AbsencePatch => 'auf Abwesenheitskorrektur',
        };

        if ($this->status === Status::Declined) $message
            ->line('Dein Antrag ' . $modelUserNotificationText .
                ($this->log instanceof Absence || $this->log instanceof AbsencePatch ?
                    ' mit dem angegebenen Grund "' . $this->log->absenceType->name . '"' : '') .
                ' für den Zeitraum vom "' .
                Carbon::parse($this->log->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->log->end)->format('d.m.Y') . '" wurde abgelehnt.')
            ->action($buttonText, $this->getNotificationURL())
            ->line('Um ihn zu öffnen, klicke bitte auf "' . $buttonText . '".');
        else if ($this->type === 'delete')  $message
            ->line('Dein Antrag auf Löschung einer Abwesenheit' .
                ($this->log instanceof Absence || $this->log instanceof AbsencePatch ?
                    ' mit dem angegebenen Grund "' . $this->log->absenceType->name . '"' : '') .
                ' für den Zeitraum vom "' .
                Carbon::parse($this->log->start)->format('d.m.Y') . '" bis zum "' .
                Carbon::parse($this->log->end)->format('d.m.Y') . '" wurde akzeptiert.');
        else  $message
            ->line('Dein Antrag ' . $modelUserNotificationText .
                ($this->log instanceof Absence || $this->log instanceof AbsencePatch ?
                    ' mit dem angegebenen Grund "' . $this->log->absenceType->name . '"' : '') .
                ' für den Zeitraum vom "' .
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
            $this->log instanceof WorkLog
            => WorkLog::class,
            $this->log instanceof WorkLogPatch
            => WorkLogPatch::class,
            $this->log instanceof TravelLog
            => TravelLog::class,
            $this->log instanceof TravelLogPatch
            => TravelLogPatch::class,
            $this->log instanceof Absence
            => Absence::class,
            $this->log instanceof AbsencePatch
            => AbsencePatch::class,
            default => throw new Exception('Invalid model type for dispute notification.'),
        };

        $modelText = match ($modelClass) {
            WorkLog::class => 'einer Arbeitszeitbuchung',
            WorkLogPatch::class => 'einer Arbeitszeitkorrektur',
            TravelLog::class => 'einer Dienstreise',
            TravelLogPatch::class => 'einer Dienstreisenkorrektur',
            Absence::class => 'einer Abwesenheit',
            AbsencePatch::class => 'einer Abwesenheitskorrektur',
        };


        $text  = match ($this->type) {
            'delete' => 'Ein Antrag auf Löschung ' . $modelText . ' von dir wurde',
            'create' => 'Ein Antrag  ' . $modelText . ' von dir wurde'
        };

        return [
            'title' => $text . ' ' . ($this->status === Status::Accepted ? 'akzeptiert.' : ($this->status == Status::Created ? 'beantragt.' : 'abgelehnt.')),
            'log_id' => $this->log->id,
            'log_model' => $modelClass,
            'type' => $this->type,
            'url' =>  $this->getNotificationURL(),
            'triggered_by' => Auth::id()
        ];
    }

    public function getNotificationURL()
    {
        return match (true) {
            $this->log instanceof Absence =>
            route('absence.index', [
                'openAbsence' => $this->log->id,
            ]),

            $this->log instanceof AbsencePatch =>
            route('absence.index', [
                'openAbsencePatch' => $this->log->id,
            ]),

            $this->log instanceof WorkLogPatch =>
            route('user.workLog.index', [
                'user' =>  $this->log->user_id,
                'openWorkLogPatch' => $this->log->id,
            ]),

            $this->log instanceof WorkLog =>
            route('user.workLog.index', [
                'user' => $this->log->user_id,
                'workLog' => $this->log->id
            ]),

            //TODO: TravelLogs still missing
        };

        return null;
    }
}
