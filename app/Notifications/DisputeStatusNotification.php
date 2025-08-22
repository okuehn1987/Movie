<?php

namespace App\Notifications;

use App\Models\Absence;
use App\Models\AbsencePatch;
use App\Models\TravelLog;
use App\Models\TravelLogPatch;
use App\Models\User;
use App\Models\WorkLog;
use App\Models\WorkLogPatch;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class DisputeStatusNotification extends Notification
{
    use Queueable;

    protected $user, $log, $status, $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Model $log, string $status, string $type = 'create')
    {
        $this->user = $user;
        $this->log = $log;
        $this->status = $status;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        //
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
            'title' => $text . ' ' . ($this->status === 'accepted' ? 'akzeptiert.' : 'abgelehnt.'),
            'log_id' => $this->log->id,
            'log_model' => $modelClass,
            'type' => $this->type
        ];
    }
}
