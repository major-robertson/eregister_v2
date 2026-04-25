<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Exceptions\InvalidStatusTransitionException;
use App\Models\EmailSequence;
use App\Models\User;
use Database\Factories\Lien\LienFilingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LienFiling extends Model implements HasMedia
{
    use BelongsToBusiness, HasFactory, InteractsWithMedia;

    protected static function newFactory(): LienFilingFactory
    {
        return LienFilingFactory::new();
    }

    protected $fillable = [
        'public_id',
        'business_id',
        'project_id',
        'document_type_id',
        'project_deadline_id',
        'service_level',
        'status',
        'needs_review',
        'jurisdiction_state',
        'jurisdiction_county',
        'amount_claimed_cents',
        'description_of_work',
        'payload_json',
        'parties_snapshot_json',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'paid_at',
        'mailed_at',
        'mailing_tracking_number',
        'recorded_at',
        'completed_at',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'service_level' => ServiceLevel::class,
            'status' => FilingStatus::class,
            'needs_review' => 'boolean',
            'amount_claimed_cents' => 'integer',
            'payload_json' => 'array',
            'parties_snapshot_json' => 'array',
            'paid_at' => 'datetime',
            'mailed_at' => 'datetime',
            'recorded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $filing): void {
            if (! $filing->public_id) {
                $filing->public_id = Str::ulid()->toBase32();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png'])
            ->useDisk('s3');

        $this->addMediaCollection('generated')
            ->useDisk('s3');

        $this->addMediaCollection('proofs')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png'])
            ->useDisk('s3');
    }

    /**
     * Scope for admin search across project, business, and creator fields.
     */
    public function scopeAdminSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->whereHas('project', function (Builder $pq) use ($term) {
                $pq->where('name', 'like', "%{$term}%")
                    ->orWhere('jobsite_address1', 'like', "%{$term}%")
                    ->orWhere('jobsite_city', 'like', "%{$term}%")
                    ->orWhere('jobsite_state', 'like', "%{$term}%")
                    ->orWhere('jobsite_zip', 'like', "%{$term}%")
                    ->orWhere('jobsite_county', 'like', "%{$term}%");
            })
                ->orWhereHas('project.business', function (Builder $bq) use ($term) {
                    $bq->where('name', 'like', "%{$term}%");
                })
                ->orWhereHas('createdBy', function (Builder $uq) use ($term) {
                    $uq->where('email', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%");
                });
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(LienProject::class, 'project_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(LienDocumentType::class, 'document_type_id');
    }

    public function projectDeadline(): BelongsTo
    {
        return $this->belongsTo(LienProjectDeadline::class, 'project_deadline_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(LienFilingRecipient::class, 'filing_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(LienFilingEvent::class, 'filing_id');
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(\App\Models\Payment::class, 'purchasable')->latestOfMany();
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(\App\Models\Payment::class, 'purchasable');
    }

    public function fulfillmentTask(): HasOne
    {
        return $this->hasOne(LienFulfillmentTask::class, 'filing_id');
    }

    /**
     * Check if the filing has been paid.
     */
    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /**
     * Check if the filing is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === FilingStatus::Complete;
    }

    /**
     * Check if the filing is full service.
     */
    public function isFullService(): bool
    {
        return $this->service_level === ServiceLevel::FullService;
    }

    /**
     * Get allowed status transitions from current status.
     */
    public function allowedTransitions(): array
    {
        return match ($this->status) {
            FilingStatus::Draft => [FilingStatus::AwaitingPayment, FilingStatus::Canceled],
            FilingStatus::AwaitingPayment => [FilingStatus::Paid, FilingStatus::Draft, FilingStatus::Canceled],
            FilingStatus::Paid => [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::WaitingOnNextStep, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::SubmittedForRecording, FilingStatus::Mailed, FilingStatus::Complete],
            FilingStatus::AwaitingClient => [FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::Hold, FilingStatus::Canceled],
            FilingStatus::AwaitingEsign => [FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Canceled],
            FilingStatus::AwaitingNotary => [FilingStatus::AwaitingClient, FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Canceled],
            FilingStatus::NeedsReview => [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::ReadyToFile, FilingStatus::WaitingOnNextStep, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::SubmittedForRecording, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Complete, FilingStatus::Canceled],
            FilingStatus::ReadyToFile => [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::NeedsReview, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::SubmittedForRecording, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Complete, FilingStatus::Canceled],
            FilingStatus::WaitingOnNextStep => [FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::Hold, FilingStatus::InFulfillment, FilingStatus::SubmittedForRecording, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Complete, FilingStatus::Canceled],
            FilingStatus::Hold => [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::WaitingOnNextStep, FilingStatus::InFulfillment, FilingStatus::SubmittedForRecording, FilingStatus::Mailed, FilingStatus::Recorded, FilingStatus::Complete, FilingStatus::Canceled],
            FilingStatus::InFulfillment => [FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary, FilingStatus::NeedsReview, FilingStatus::ReadyToFile, FilingStatus::WaitingOnNextStep, FilingStatus::Hold, FilingStatus::SubmittedForRecording, FilingStatus::Mailed, FilingStatus::Complete, FilingStatus::Canceled],
            FilingStatus::SubmittedForRecording => [FilingStatus::Recorded, FilingStatus::Hold, FilingStatus::NeedsReview, FilingStatus::Canceled],
            FilingStatus::Mailed => [FilingStatus::NeedsReview, FilingStatus::Hold, FilingStatus::Complete],
            FilingStatus::Recorded => [FilingStatus::NeedsReview, FilingStatus::Hold, FilingStatus::Mailed, FilingStatus::Complete, FilingStatus::Canceled],
            FilingStatus::Complete => [FilingStatus::NeedsReview, FilingStatus::Hold],
            FilingStatus::Canceled => [FilingStatus::NeedsReview],
            FilingStatus::Refunded => [],
        };
    }

    /**
     * Check if a transition to the given status is allowed.
     */
    public function canTransitionTo(FilingStatus $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }

    /**
     * Transition to a new status, validating the transition and creating an event.
     *
     * @throws InvalidStatusTransitionException
     */
    public function transitionTo(FilingStatus $newStatus, array $meta = []): void
    {
        if (! $this->canTransitionTo($newStatus)) {
            throw new InvalidStatusTransitionException($this->status, $newStatus);
        }

        $oldStatus = $this->status;

        // Update timestamp based on new status
        $updates = ['status' => $newStatus];
        if ($newStatus === FilingStatus::Paid && ! $this->paid_at) {
            $updates['paid_at'] = now();
        } elseif ($newStatus === FilingStatus::Mailed && ! $this->mailed_at) {
            $updates['mailed_at'] = now();
            if (isset($meta['tracking_number'])) {
                $updates['mailing_tracking_number'] = $meta['tracking_number'];
            }
        } elseif ($newStatus === FilingStatus::Recorded && ! $this->recorded_at) {
            $updates['recorded_at'] = now();
        } elseif ($newStatus === FilingStatus::Complete && ! $this->completed_at) {
            $updates['completed_at'] = now();
        }

        $this->update($updates);

        // Create event
        $this->events()->create([
            'business_id' => $this->business_id,
            'event_type' => 'status_changed',
            'payload_json' => [
                'from' => $oldStatus->value,
                'to' => $newStatus->value,
                'meta' => $meta,
            ],
            'created_by' => auth()->id(),
        ]);

        $this->syncActionReminderSequence($oldStatus, $newStatus);
    }

    /**
     * Start or suppress the filing action reminder sequence based on status transition.
     */
    protected function syncActionReminderSequence(FilingStatus $oldStatus, FilingStatus $newStatus): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        if ($newStatus->isWaitingOnCustomer()) {
            $user = $this->createdBy ?? User::find($this->created_by_user_id);

            if ($user) {
                EmailSequence::startReminderFor($this, $newStatus, $user, $this->business);
            }
        } elseif ($oldStatus->isWaitingOnCustomer()) {
            EmailSequence::suppressReminderFor($this);
        }
    }

    /**
     * Format the amount claimed for display.
     */
    public function formattedAmountClaimed(): ?string
    {
        if ($this->amount_claimed_cents === null) {
            return null;
        }

        return '$'.number_format($this->amount_claimed_cents / 100, 2);
    }
}
