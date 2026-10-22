<?php

namespace App\Domains\Admin\Livewire\Concerns;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

/**
 * For admin detail pages: the emails the app sent about the page's record,
 * merged into its Activity history, and a viewer that opens one as it was
 * sent (EREG-154). The page includes admin.partials.sent-email-modal.
 */
trait ShowsSentEmails
{
    public ?int $viewingEmailId = null;

    public bool $showEmailModal = false;

    /**
     * The records whose emails this page shows.
     *
     * @return array<int, Model>
     */
    abstract protected function sentEmailRecords(): array;

    public function viewEmail(int $emailId): void
    {
        // Only an email about this page's record opens here.
        $this->viewingEmailId = EmailLog::query()
            ->aboutAny(...$this->sentEmailRecords())
            ->whereKey($emailId)
            ->value('id');

        $this->showEmailModal = $this->viewingEmailId !== null;
        unset($this->viewingEmail);
    }

    #[Computed]
    public function viewingEmail(): ?EmailLog
    {
        if ($this->viewingEmailId === null) {
            return null;
        }

        return EmailLog::query()
            ->aboutAny(...$this->sentEmailRecords())
            ->find($this->viewingEmailId);
    }

    /**
     * The page's activity entries with its emails merged in, newest first.
     * An email that an entry already announces ($announcedBy: event_type =>
     * mailable class, such as the request an admin wrote) rides on that
     * entry as its sentEmail relation instead of taking a row of its own.
     *
     * @param  array<string, class-string>  $announcedBy
     */
    protected function withSentEmails(Collection $entries, array $announcedBy = []): Collection
    {
        $emails = EmailLog::query()
            ->aboutAny(...$this->sentEmailRecords())
            ->select(['id', 'mailable', 'subject', 'to', 'cc', 'status', 'status_detail', 'body_withheld', 'created_at'])
            ->latest()
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $claimed = [];
        $oldestFirst = $emails->sortBy('created_at');

        foreach ($entries->sortBy('created_at') as $entry) {
            $class = $announcedBy[$entry->event_type ?? ''] ?? null;

            if ($class === null) {
                continue;
            }

            // Queued after the entry's transaction commits, so within a
            // minute before (clock skew) to a day after it.
            $email = $oldestFirst->first(fn (EmailLog $email) => $email->mailable === $class
                && ! isset($claimed[$email->id])
                && $email->created_at->gte($entry->created_at->subMinute())
                && $email->created_at->lte($entry->created_at->addDay()));

            if ($email !== null) {
                $claimed[$email->id] = true;
                $entry->setRelation('sentEmail', $email);
            }
        }

        return $entries
            ->concat($emails->reject(fn (EmailLog $email) => isset($claimed[$email->id])))
            ->sortByDesc('created_at')
            ->values();
    }
}
