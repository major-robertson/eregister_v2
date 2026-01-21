<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\FilingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienFilingEvent extends Model
{
    use BelongsToBusiness, HasFactory;

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
            'document_uploaded' => 'Document uploaded',
            'recipient_added' => 'Recipient added',
            default => ucfirst(str_replace('_', ' ', $this->event_type)),
        };
    }

    /**
     * Get icon name for the event type.
     */
    public function statusIcon(): string
    {
        if ($this->event_type !== 'status_changed') {
            return 'info';
        }

        $toStatus = FilingStatus::tryFrom($this->payload_json['to'] ?? '');

        return $toStatus?->icon() ?? 'info';
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
