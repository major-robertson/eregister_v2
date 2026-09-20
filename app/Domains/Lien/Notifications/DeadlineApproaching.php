<?php

namespace App\Domains\Lien\Notifications;

use App\Domains\Lien\Models\LienProjectDeadline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class DeadlineApproaching extends Notification implements ShouldQueue
{
    use Queueable;

    /** Missing one of these can cost the customer their lien rights; the email says so. */
    private const LIEN_RIGHTS_DOCUMENTS = ['prelim_notice', 'noi', 'mechanics_lien'];

    /**
     * @param  int  $daysRemaining  Days until the due date on the day this was sent (0 = due today).
     */
    public function __construct(
        public LienProjectDeadline $deadline,
        public int $daysRemaining
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $document = $this->deadline->documentType->name;
        $project = $this->deadline->project->name;
        $dueDate = $this->deadline->due_date->format('F j, Y');

        $when = match ($this->daysRemaining) {
            0 => 'today',
            1 => 'tomorrow',
            default => "in {$this->daysRemaining} days",
        };

        $message = (new MailMessage)
            ->subject("{$document} due {$when}: {$project}")
            ->greeting('Hi '.($notifiable->first_name ?: 'there').',')
            ->line("Your {$document} for **{$project}** is due {$when}, on {$dueDate}.");

        if (in_array($this->deadline->documentType->slug, self::LIEN_RIGHTS_DOCUMENTS, true)) {
            $message->line('If you miss this deadline, you can lose your lien rights on this job.');
        }

        return $message
            ->action("Start my {$document}", route('lien.filings.start', [
                'project' => $this->deadline->project,
                'deadline' => $this->deadline,
            ]))
            // No promise of a same-day turnaround when it is due today or tomorrow.
            ->line($this->daysRemaining <= 1 ? 'Time is short, so please start now.' : 'We can prepare and send it for you.')
            ->line('We calculate this date from the dates you entered, so please double-check it.')
            ->line('[Turn off deadline reminders]('.URL::signedRoute('email.preferences', ['user' => $notifiable->getKey()]).')');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'deadline_id' => $this->deadline->id,
            'project_id' => $this->deadline->project_id,
            'document_type' => $this->deadline->documentType->name,
            'days_remaining' => $this->daysRemaining,
            'due_date' => $this->deadline->due_date->toDateString(),
        ];
    }
}
