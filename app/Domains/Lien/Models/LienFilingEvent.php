<?php

namespace App\Domains\Lien\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\FilingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienFilingEvent extends Model
{
    use BelongsToBusiness, HasFactory;

    /** The wizard logged a date change after telling the customer the deadline had passed. */
    public const DATES_CHANGED_AFTER_DEADLINE_BLOCK = 'dates_changed_after_deadline_block';

    protected $fillable = [
        'business_id',
        'filing_id',
        'event_type',
        'payload_json',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
        ];
    }

    public function filing(): BelongsTo
    {
        return $this->belongsTo(LienFiling::class, 'filing_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get a meta value from the payload.
     */
    public function meta(string $key, mixed $default = null): mixed
    {
        return $this->payload_json['meta'][$key] ?? $default;
    }

    /**
     * Check if a meta key exists.
     */
    public function hasMeta(string $key): bool
    {
        return isset($this->payload_json['meta'][$key]);
    }

    /**
     * Get a human-readable description of this event.
     */
    public function description(): string
    {
        return match ($this->event_type) {
            'status_changed' => $this->statusChangeDescription(),
            'note_added' => 'Note added',
            'document_uploaded' => 'Customer uploaded a document',
            'recipient_added' => 'Recipient added',
            'application_project_updated' => 'Project details updated',
            'application_filing_updated' => 'Filing details updated',
            'application_parties_updated' => 'Parties updated',
            'esign_sent' => 'Sent for e-signature',
            'esign_reminder_sent' => 'E-signature reminder sent',
            'esign_completed' => 'E-signature completed',
            'client_request_sent' => 'Request sent to customer',
            'client_response' => 'Customer answered',
            'draft_sent_for_review' => 'Draft sent for review',
            'draft_approved' => 'Draft approved',
            'draft_changes_requested' => 'Changes requested',
            'customer_emailed' => 'Customer emailed us',
            'reply_dismissed' => 'Marked still waiting',
            self::DATES_CHANGED_AFTER_DEADLINE_BLOCK => 'Dates changed after a deadline notice',
            default => ucfirst(str_replace('_', ' ', $this->event_type)),
        };
    }

    /**
     * Get icon name for the event type.
     */
    public function statusIcon(): string
    {
        if ($this->event_type !== 'status_changed') {
            return 'information-circle';
        }

        $toStatus = FilingStatus::tryFrom($this->payload_json['to'] ?? '');

        return $toStatus?->icon() ?? 'information-circle';
    }

    private function statusChangeDescription(): string
    {
        $from = FilingStatus::tryFrom($this->payload_json['from'] ?? '');
        $to = FilingStatus::tryFrom($this->payload_json['to'] ?? '');

        if (! $to) {
            return 'Status changed';
        }

        return 'Status changed to '.$to->label();
    }
}
