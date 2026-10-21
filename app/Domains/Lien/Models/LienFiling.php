<?php

namespace App\Domains\Lien\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Exceptions\InvalidStatusTransitionException;
use App\Mail\FilingActionRequested;
use App\Mail\FilingMilestone;
use App\Models\EmailSequence;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonInterface;
use Database\Factories\Lien\LienFilingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LienFiling extends Model implements HasMedia
{
    use BelongsToBusiness, HasFactory, InteractsWithMedia, SoftDeletes;

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
        'document_details_json',
        'client_request_json',
        'customer_emailed_at',
        'customer_emailed_front_url',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'paid_at',
        'mailed_at',
        'mailing_tracking_number',
        'recording_method',
        'recording_provider',
        'recording_reference',
        'recording_submitted_at',
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
            'document_details_json' => 'array',
            'client_request_json' => 'array',
            'customer_emailed_at' => 'datetime',
            'paid_at' => 'datetime',
            'mailed_at' => 'datetime',
            'recording_method' => RecordingMethod::class,
            'recording_submitted_at' => 'datetime',
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

        static::deleting(function (self $filing): void {
            if ($filing->isForceDeleting()) {
                return;
            }

            EmailSequence::suppressReminderFor($filing);
            EmailSequence::suppressAllFor($filing, 'filing_deleted');
        });

        // A refunded, canceled or finished filing has nothing left to email
        // the customer about: stop every sequence on it (reminders, "finish
        // your order"). This watches the column rather than transitionTo()
        // because refunds set the status directly (RefundPayment,
        // StripeRefundRecorder).
        static::updated(function (self $filing): void {
            if (! $filing->wasChanged('status')) {
                return;
            }

            $reason = match ($filing->status) {
                FilingStatus::Refunded => 'filing_refunded',
                FilingStatus::Canceled => 'filing_canceled',
                FilingStatus::Complete => 'filing_complete',
                default => null,
            };

            if ($reason !== null) {
                EmailSequence::suppressAllFor($filing, $reason);
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

        // A PDF an admin attaches to a request for the customer (emailed
        // with it and linked on the filing page).
        $this->addMediaCollection('requests')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('s3');

        // Files the customer sends when answering a request. FilingShow
        // validates the type (PDF, JPG, PNG, HEIC); no list here because
        // phones label HEIC photos inconsistently.
        $this->addMediaCollection('customer_uploads')
            ->useDisk('s3');

        // Drafts an admin sends the customer to approve (EREG-140). Every
        // version is kept; custom properties: version (1, 2, ...),
        // uploaded_by_user_id and source ('upload', or 'generated' when
        // copied from the generated document).
        $this->addMediaCollection('drafts')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('s3');

        // The e-signed copy of a draft (EREG-141, UploadedDocumentSignable):
        // the draft with the signature and certificate page appended, one per
        // completed signature request. Custom properties: signature_request_id,
        // version (the draft version signed), draft_media_id, sha256.
        $this->addMediaCollection('signed')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('s3');

        // The county's recorded copy and our proof of service, uploaded on
        // the admin status form at Recorded / Mailed / Complete (EREG-130),
        // emailed with the milestone email and linked on the filing page.
        // Every upload is kept; the newest is the one emailed.
        $this->addMediaCollection('recorded')
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('s3');

        $this->addMediaCollection('proof_of_service')
            ->acceptsMimeTypes(['application/pdf'])
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

    /**
     * The customer's newest answer to us: a client_response (answered on the
     * filing page), document_uploaded, draft_approved or
     * draft_changes_requested event. Eager-load it for lists.
     */
    public function latestCustomerReply(): HasOne
    {
        return $this->hasOne(LienFilingEvent::class, 'filing_id')->ofMany(
            ['created_at' => 'max', 'id' => 'max'],
            fn (Builder $query) => $query->whereIn('event_type', ['client_response', 'document_uploaded', 'draft_approved', 'draft_changes_requested']),
        );
    }

    /**
     * When the customer last answered us, or null: on the filing page
     * (latestCustomerReply) or by email (customer_emailed_at), whichever is
     * newest. The lien board shows it ("Answered", "Customer answered ...")
     * and turns the card red when nothing from us follows within a business
     * day.
     */
    public function customerRepliedAt(): ?CarbonInterface
    {
        $answeredAt = $this->latestCustomerReply?->created_at;
        $emailedAt = $this->customer_emailed_at;

        if ($answeredAt === null || $emailedAt === null) {
            return $answeredAt ?? $emailedAt;
        }

        return $emailedAt->gt($answeredAt) ? $emailedAt : $answeredAt;
    }

    /**
     * Whether the customer emailed us about this filing and no one has acted
     * on it yet (a status change or "Still waiting" clears it).
     */
    public function hasCustomerEmail(): bool
    {
        return $this->customer_emailed_at !== null;
    }

    /**
     * The customer emailed us about this filing (Front webhook, EREG-132).
     * Flag it for the agent with the Front link, log a customer_emailed event
     * (one per conversation per 10 minutes; later emails only move the
     * timestamp) and, while the filing waits on the customer, pause its
     * reminders. The status stays where it is: the agent decides. Nothing
     * from the message itself is stored.
     */
    public function recordCustomerEmail(string $conversationId, string $frontUrl, string $sender): void
    {
        DB::transaction(function () use ($conversationId, $frontUrl, $sender) {
            $this->update([
                'customer_emailed_at' => now(),
                'customer_emailed_front_url' => $frontUrl,
            ]);

            $alreadyLogged = $this->events()
                ->withoutGlobalScope('business')
                ->where('event_type', 'customer_emailed')
                ->where('payload_json->conversation_id', $conversationId)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->exists();

            if (! $alreadyLogged) {
                $this->events()->create([
                    'business_id' => $this->business_id,
                    'event_type' => 'customer_emailed',
                    'payload_json' => [
                        'conversation_id' => $conversationId,
                        'front_url' => $frontUrl,
                        'sender' => $sender,
                        'status' => $this->status->value,
                    ],
                    'created_by' => null,
                ]);
            }

            if ($this->status->isWaitingOnCustomer()) {
                EmailSequence::pauseReminderFor($this, 'customer_emailed');
            }
        });
    }

    /**
     * E-signature sessions for this filing (polymorphic). Each "Send for E-Sign"
     * creates one; only one is active at a time.
     */
    public function signatureRequests(): MorphMany
    {
        return $this->morphMany(SignatureRequest::class, 'signable');
    }

    public function activeSignatureRequest(): ?SignatureRequest
    {
        return $this->signatureRequests()->active()->latest('id')->first();
    }

    public function latestSignatureRequest(): ?SignatureRequest
    {
        return $this->signatureRequests()->latest('id')->first();
    }

    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'purchasable')->latestOfMany();
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'purchasable');
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
     * Whether this filing is for a Payment Demand Letter. Gates the admin
     * "Download Demand Letter" affordance; more document-type predicates can
     * follow the same shape as additional generators land.
     */
    public function isDemandLetter(): bool
    {
        return $this->documentType?->slug === 'demand_letter';
    }

    /**
     * The registry kind this filing's generated document renders through
     * (prelim_notice, noi, mechanics_lien, lien_release), or null for demand
     * letters and anything else without one.
     */
    public function documentKind(): ?string
    {
        $slug = $this->documentType?->slug;

        return in_array($slug, LienDocumentRegistry::KINDS, true) ? $slug : null;
    }

    /**
     * Whether the admin page shows the Documents card for this filing.
     */
    public function hasGeneratedDocuments(): bool
    {
        return $this->documentKind() !== null;
    }

    /**
     * Check if the filing is full service.
     */
    public function isFullService(): bool
    {
        return $this->service_level === ServiceLevel::FullService;
    }

    /**
     * Whether this filing's immutable fulfillment snapshot can still be re-synced
     * from edited application data. True only once a snapshot exists (i.e. the filing
     * has been through payment) and before the document is out the door. Draft /
     * AwaitingPayment filings have no snapshot yet, so they are naturally excluded.
     */
    public function canApplicationSnapshotBeResynced(): bool
    {
        return $this->payload_json !== null && ! $this->status->isFulfillmentLocked();
    }

    /**
     * Scope to filings whose fulfillment snapshot can still be re-synced: a snapshot
     * exists and the status is not fulfillment-locked. Used to find the sibling
     * filings on a project that should be updated when application data is corrected.
     */
    public function scopeSnapshotEditable(Builder $query): Builder
    {
        return $query->whereNotNull('payload_json')
            ->whereNotIn('status', FilingStatus::fulfillmentLockedValues());
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
     * Moving into a waiting status (Awaiting Client / E-Signature / Notary)
     * with $meta['customer_message'] (and optionally
     * $meta['attachment_media_id'], a 'requests' media id, or for a draft
     * review request_type 'review', draft_media_id and needs_signature,
     * see SendDraftForReview) opens a request:
     * it is stored in client_request_json and emailed to the filing's creator
     * at once (see ChangeFilingStatus). Leaving a waiting status closes the
     * open request. Any status change clears the "customer emailed" flag
     * (recordCustomerEmail). Moving a full-service filing to Submitted for
     * Recording, Recorded, Mailed to Parties or Complete emails the customer
     * (FilingMilestone), with the recorded copy / proof of service that
     * ChangeFilingStatus stored just before.
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
        } elseif ($newStatus === FilingStatus::Mailed) {
            if (! $this->mailed_at) {
                $updates['mailed_at'] = now();
            }
            // A tracking number given now wins, also when mailing again.
            if (filled($meta['tracking_number'] ?? null)) {
                $updates['mailing_tracking_number'] = $meta['tracking_number'];
            }
        } elseif ($newStatus === FilingStatus::Recorded && ! $this->recorded_at) {
            $updates['recorded_at'] = now();
        } elseif ($newStatus === FilingStatus::Complete && ! $this->completed_at) {
            $updates['completed_at'] = now();
        }

        // Leaving a waiting status closes the open request. The text stays
        // for history (the admin page shows it with the customer's answer).
        $request = $this->client_request_json;
        if ($oldStatus->isWaitingOnCustomer() && is_array($request) && ($request['resolved_at'] ?? null) === null) {
            $updates['client_request_json'] = array_merge($request, ['resolved_at' => now()->toIso8601String()]);
        }

        // Any status change is the agent acting on the customer's email.
        $updates['customer_emailed_at'] = null;
        $updates['customer_emailed_front_url'] = null;

        $opensRequest = $newStatus->isWaitingOnCustomer() && filled($meta['customer_message'] ?? null);
        if ($opensRequest) {
            $updates['client_request_json'] = [
                'text' => $meta['customer_message'],
                'requested_at' => now()->toIso8601String(),
                'requested_by_user_id' => auth()->id(),
                'attachment_media_id' => $meta['attachment_media_id'] ?? null,
                'status' => $newStatus->value,
                'resolved_at' => null,
            ];

            // A draft sent for review (SendDraftForReview): the customer
            // approves it or asks for changes on the filing page.
            if (($meta['request_type'] ?? null) === 'review') {
                $updates['client_request_json'] += [
                    'type' => 'review',
                    'draft_media_id' => $meta['draft_media_id'] ?? null,
                    'needs_signature' => (bool) ($meta['needs_signature'] ?? false),
                ];
            }
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

        if ($opensRequest) {
            $this->sendClientRequest();
        }

        $this->sendMilestoneEmail($newStatus);
    }

    /**
     * Email the filing's creator that it reached Submitted for Recording,
     * Recorded, Mailed to Parties or Complete (FilingMilestone::sendsFor:
     * full-service filings only). Queued after commit, so a rolled-back
     * change sends nothing.
     */
    protected function sendMilestoneEmail(FilingStatus $status): void
    {
        if (! FilingMilestone::sendsFor($this, $status)) {
            return;
        }

        $user = $this->createdBy ?? User::find($this->created_by_user_id);

        if ($user) {
            Mail::to($user)->queue(new FilingMilestone($this, $status));
        }
    }

    /**
     * The request we sent the customer, while the filing still waits on it:
     * {text, requested_at, requested_by_user_id, attachment_media_id, status,
     * resolved_at}, plus {type: 'review', draft_media_id, needs_signature}
     * for a draft sent for review. Null once answered or the filing has
     * moved on, and for filings moved to a waiting status before requests
     * were stored.
     */
    public function openClientRequest(): ?array
    {
        $request = $this->client_request_json;

        if (! is_array($request) || ($request['resolved_at'] ?? null) !== null || ! $this->status->isWaitingOnCustomer()) {
            return null;
        }

        return $request;
    }

    /**
     * The open request when it is a draft sent for review, else null.
     */
    public function openReviewRequest(): ?array
    {
        $request = $this->openClientRequest();

        return ($request['type'] ?? null) === 'review' ? $request : null;
    }

    /**
     * The draft the latest review request was about, if it still exists.
     */
    public function reviewDraft(): ?Media
    {
        $mediaId = $this->client_request_json['draft_media_id'] ?? null;

        return $mediaId
            ? $this->media()->where('collection_name', 'drafts')->whereKey($mediaId)->first()
            : null;
    }

    /**
     * Every draft version, newest first.
     *
     * @return EloquentCollection<int, Media>
     */
    public function draftVersions(): EloquentCollection
    {
        return $this->media()
            ->where('collection_name', 'drafts')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * The number the next draft gets (1 for the first).
     */
    public function nextDraftVersion(): int
    {
        return (int) $this->draftVersions()->max(fn (Media $draft) => (int) $draft->getCustomProperty('version', 0)) + 1;
    }

    /**
     * The PDF attached to the latest request, if any. Queried rather than
     * read from the loaded media relation, which does not pick up a file
     * added a moment ago (the request email is built right after).
     */
    public function clientRequestAttachment(): ?Media
    {
        $mediaId = $this->client_request_json['attachment_media_id'] ?? null;

        return $mediaId
            ? $this->media()->where('collection_name', 'requests')->whereKey($mediaId)->first()
            : null;
    }

    /**
     * The newest recorded copy ('recorded') or proof of service
     * ('proof_of_service'). Queried, like clientRequestAttachment(), so the
     * milestone email built right after the upload finds it.
     */
    public function latestMilestoneFile(string $collection): ?Media
    {
        return $this->media()
            ->where('collection_name', $collection)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * The admin who sent the latest request, if known.
     */
    public function clientRequestSender(): ?User
    {
        $userId = $this->client_request_json['requested_by_user_id'] ?? null;

        return $userId ? User::find($userId) : null;
    }

    public function clientRequestSentAt(): ?Carbon
    {
        $requestedAt = $this->client_request_json['requested_at'] ?? null;

        return $requestedAt ? Carbon::parse($requestedAt) : null;
    }

    /**
     * Email the request just opened to the filing's creator, and log it.
     * The mail is queued after commit, so a rolled-back change sends nothing.
     */
    protected function sendClientRequest(): void
    {
        $user = $this->createdBy ?? User::find($this->created_by_user_id);

        if (! $user) {
            return;
        }

        $request = $this->client_request_json;

        $this->events()->create([
            'business_id' => $this->business_id,
            'event_type' => 'client_request_sent',
            'payload_json' => [
                'text' => $request['text'],
                'attachment_media_id' => $request['attachment_media_id'],
                'status' => $request['status'],
                'type' => $request['type'] ?? null,
                'sent_to' => $user->email,
            ],
            'created_by' => auth()->id(),
        ]);

        Mail::to($user)->queue(new FilingActionRequested($this));
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
