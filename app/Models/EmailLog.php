<?php

namespace App\Models;

use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Models\LienProjectDeadline;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * One email the app sent (EREG-154). LogSentEmail writes the row when the
 * message goes out; the Postmark webhook adds delivery, bounces and spam
 * complaints. The admin lien filing and sales tax pages list a record's
 * emails in their Activity history.
 */
class EmailLog extends Model
{
    use HasFactory;

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_BOUNCED = 'bounced';

    public const STATUS_COMPLAINED = 'complained';

    /** Postmark metadata keys (20 characters at most) naming the record. */
    public const META_TYPE = 'record_type';

    public const META_ID = 'record_id';

    protected $fillable = [
        'mailable',
        'subject',
        'from_address',
        'to',
        'cc',
        'bcc',
        'user_id',
        'related_type',
        'related_id',
        'message_id',
        'status',
        'status_detail',
        'delivered_at',
        'bounced_at',
        'html_body',
        'text_body',
        'body_withheld',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'attachments' => 'array',
            'body_withheld' => 'boolean',
            'delivered_at' => 'datetime',
            'bounced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Metadata for a mailable's envelope naming the record the email is
     * about: `metadata: EmailLog::about($filing)`. LogSentEmail reads it back
     * to link the row, and Postmark lists it on the message too. A sequence,
     * payment, signature request or deadline stands for the record it serves.
     * Never throws: it runs in mailable constructors, inside checkouts.
     *
     * @return array<string, string>
     */
    public static function about(?Model $record): array
    {
        try {
            $record = match (true) {
                $record instanceof EmailSequence => $record->sequenceable,
                $record instanceof Payment => $record->purchasable,
                $record instanceof SignatureRequest => $record->signable,
                $record instanceof LienProjectDeadline => $record->project,
                default => $record,
            };

            if ($record === null || ! $record->exists) {
                return [];
            }

            return [
                self::META_TYPE => $record->getMorphClass(),
                self::META_ID => (string) $record->getKey(),
            ];
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * Emails about any of the given records.
     */
    public function scopeAboutAny(Builder $query, Model ...$records): Builder
    {
        return $query->where(function (Builder $query) use ($records) {
            foreach ($records as $record) {
                $query->orWhere(fn (Builder $query) => $query
                    ->where('related_type', $record->getMorphClass())
                    ->where('related_id', $record->getKey()));
            }

            if ($records === []) {
                $query->whereRaw('1 = 0');
            }
        });
    }

    /**
     * A Postmark Delivery, Bounce or SpamComplaint webhook event. Only the
     * To and Cc recipients count: a delivery to a Bcc copy says nothing
     * about the customer's. The newest event wins, except a spam complaint,
     * which stays.
     *
     * @param  array<string, mixed>  $event
     */
    public static function recordPostmarkEvent(array $event): void
    {
        $messageId = $event['MessageID'] ?? null;
        $recipient = $event['Recipient'] ?? $event['Email'] ?? null;

        if (! is_string($messageId) || $messageId === '' || ! is_string($recipient)) {
            return;
        }

        $log = static::query()->where('message_id', $messageId)->first();

        if ($log === null || ! $log->isAddressedTo($recipient) || $log->status === self::STATUS_COMPLAINED) {
            return;
        }

        $at = self::eventTime($event['DeliveredAt'] ?? $event['BouncedAt'] ?? null);
        $latest = collect([$log->delivered_at, $log->bounced_at])->filter()->max();

        if ($latest !== null && $at->lt($latest)) {
            return;
        }

        match ($event['RecordType'] ?? null) {
            'Delivery' => $log->update([
                'status' => self::STATUS_DELIVERED,
                'status_detail' => null,
                'delivered_at' => $at,
            ]),
            'Bounce' => $log->update([
                'status' => self::STATUS_BOUNCED,
                'status_detail' => Str::limit((string) ($event['Name'] ?? $event['Description'] ?? ''), 250) ?: null,
                'bounced_at' => $at,
            ]),
            'SpamComplaint' => $log->update([
                'status' => self::STATUS_COMPLAINED,
                'status_detail' => null,
                'bounced_at' => $at,
            ]),
            default => null,
        };
    }

    public function isAddressedTo(string $email): bool
    {
        $email = Str::lower(trim($email));

        return collect([...($this->to ?? []), ...($this->cc ?? [])])
            ->contains(fn (string $address) => Str::lower($address) === $email);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_BOUNCED => 'Bounced',
            self::STATUS_COMPLAINED => 'Marked as spam',
            default => 'Sent',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_DELIVERED => 'green',
            self::STATUS_BOUNCED, self::STATUS_COMPLAINED => 'red',
            default => 'zinc',
        };
    }

    /**
     * The To and Cc addresses, comma separated.
     */
    public function recipientList(): string
    {
        return implode(', ', [...($this->to ?? []), ...($this->cc ?? [])]);
    }

    /**
     * The stored HTML for the admin viewer, with links switched off: the
     * viewer shows what the customer saw, it is not a way into their account.
     */
    public function viewerHtml(): ?string
    {
        if ($this->html_body === null) {
            return null;
        }

        $style = '<style>a{pointer-events:none;cursor:default}</style>';

        return str_contains($this->html_body, '</head>')
            ? Str::replaceFirst('</head>', $style.'</head>', $this->html_body)
            : $style.$this->html_body;
    }

    private static function eventTime(mixed $value): Carbon
    {
        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value)->utc();
            } catch (Throwable) {
                // Fall through to now.
            }
        }

        return now();
    }
}
