<?php

namespace App\Domains\Lien\Notifications;

use App\Domains\Lien\Models\LienProjectDeadline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineApproaching extends Notification implements ShouldQueue
{
    use Queueable;

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
        $subject = $this->daysRemaining === 0
            ? "OVERDUE: {$this->deadline->documentType->name} for {$this->deadline->project->name}"
            : "{$this->deadline->documentType->name} due in {$this->daysRemaining} days";

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello!')
            ->line("Your {$this->deadline->documentType->name} deadline for project \"{$this->deadline->project->name}\" is approaching.");

        if ($this->daysRemaining === 0) {
            $message->line('**This deadline is now overdue!**');
        } else {
            $message->line("Due date: {$this->deadline->due_date->format('F j, Y')}");
        }

        $message->action('Start Filing Now', route('lien.filings.start', [
            'project' => $this->deadline->project,
            'deadline' => $this->deadline,
        ]));

        $message->line('Taking action now helps protect your lien rights.');

        return $message;
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
