<?php

namespace App\Models;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Domains\SalesTax\SalesTaxFollowUp;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'sequence_type',
        'sequenceable_type',
        'sequenceable_id',
        'customer_type',
        'trigger_status',
        'resume_url',
        'next_send_at',
        'completed_at',
        'suppressed_at',
        'suppression_reason',
        'paused_at',
        'pause_reason',
    ];

    protected function casts(): array
    {
        return [
            'next_send_at' => 'datetime',
            'completed_at' => 'datetime',
            'suppressed_at' => 'datetime',
            'paused_at' => 'datetime',
        ];
    }

    /** @var array<string, array{steps: int, delays: int[], email_prefix: string, unsubscribe_category: ?string}> */
    protected static array $sequenceConfig = [
        'abandon_checkout' => [
            'steps' => 3,
            'delays' => [60, 1440, 4320],
            'email_prefix' => 'abandon_checkout_step',
            'unsubscribe_category' => EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT,
        ],
        'filing_action_reminder' => [
            'steps' => 5,
            'delays' => [2880, 4320, 10080, 10080, 10080],
            'email_prefix' => 'filing_action_reminder_step',
            'unsubscribe_category' => null,
        ],
        // Signed up from a waiver page, no waiver yet. The waiver welcome
        // email covers the first hour, so these land on day 1 and day 3.
        'waiver_started' => [
            'steps' => 2,
            'delays' => [1440, 2880],
            'email_prefix' => 'waiver_started_step',
            'unsubscribe_category' => EmailUnsubscribe::CATEGORY_MARKETING,
        ],
        // Free-plan waiver saved: 2 hours (sign it here), day 2 (is e-sign
        // valid), day 7 (lien deadlines for the job), day 21 (next draw).
        'waiver_unsigned' => [
            'steps' => 4,
            'delays' => [120, 2880, 7200, 20160],
            'email_prefix' => 'waiver_unsigned_step',
            'unsubscribe_category' => EmailUnsubscribe::CATEGORY_MARKETING,
        ],
        // Paid for a sales tax registration, questions not sent in: day 1,
        // day 4, day 11. Service mail about an order they paid for, so no
        // unsubscribe category.
        'registration_unfinished' => [
            'steps' => 3,
            'delays' => [1440, 4320, 10080],
            'email_prefix' => 'registration_unfinished_step',
            'unsubscribe_category' => null,
        ],
        // Came for resale certificates and has not subscribed: 1 hour, then a
        // day, then three days (the abandon_checkout cadence). Someone who
        // turned off order reminders does not get these either.
        'resale_started' => [
            'steps' => 3,
            'delays' => [60, 1440, 4320],
            'email_prefix' => 'resale_started_step',
            'unsubscribe_category' => EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT,
        ],
        // Signed up through a sales tax door and has not picked a state: 1
        // hour, then a day, then three days (the abandon_checkout cadence,
        // which takes over on the order screen). Someone who turned off order
        // reminders does not get these either.
        'sales_tax_started' => [
            'steps' => 3,
            'delays' => [60, 1440, 4320],
            'email_prefix' => 'sales_tax_started_step',
            'unsubscribe_category' => EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT,
        ],
    ];

    /** Sequence types with their own stop rules (see shouldSuppressWaiverNurture). */
    public const WAIVER_NURTURE_TYPES = ['waiver_started', 'waiver_unsigned'];

    /**
     * E-signature links expire 14 days after they're sent, so e-sign reminders
     * run on days 2, 5, 8, 11 and 13 instead; the day-13 one warns that the link
     * expires in 24 hours (see FilingActionReminder).
     */
    private const ESIGN_REMINDER_DELAYS = [2880, 4320, 4320, 4320, 2880];

    /**
     * Paid lien filing states in which the order is still being worked on. A
     * business with one of these, paid in the last LIEN_ORDER_IN_PROGRESS_DAYS
     * days, gets no lien "finish your order" emails (see lienCheckoutStopReason).
     */
    private const LIEN_ORDER_IN_PROGRESS_STATUSES = [
        FilingStatus::Paid,
        FilingStatus::AwaitingClient,
        FilingStatus::AwaitingEsign,
        FilingStatus::AwaitingNotary,
        FilingStatus::NeedsReview,
        FilingStatus::ReadyToFile,
        FilingStatus::WaitingOnNextStep,
        FilingStatus::Hold,
        FilingStatus::InFulfillment,
        FilingStatus::SubmittedForRecording,
        FilingStatus::Recorded,
        FilingStatus::Mailed,
    ];

    private const LIEN_ORDER_IN_PROGRESS_DAYS = 90;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function sequenceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the config for this sequence type (e-sign reminders use their own delays).
     *
     * @return array{steps: int, delays: int[], email_prefix: string, unsubscribe_category: string}
     */
    public function config(): array
    {
        $config = static::$sequenceConfig[$this->sequence_type];

        if ($this->trigger_status === FilingStatus::AwaitingEsign->value) {
            $config['delays'] = self::ESIGN_REMINDER_DELAYS;
        }

        return $config;
    }

    /**
     * Returns the next step number to send (1-based), or null if all sent.
     */
    public function currentStep(): ?int
    {
        $config = $this->config();

        $sentCount = SentEmail::where('emailable_type', $this->getMorphClass())
            ->where('emailable_id', $this->id)
            ->where('email_type', 'like', $config['email_prefix'].'_%')
            ->count();

        return $sentCount >= $config['steps'] ? null : $sentCount + 1;
    }

    public function isPaymentCompleted(): bool
    {
        $sequenceable = $this->sequenceable;

        if (! $sequenceable) {
            return true;
        }

        if (method_exists($sequenceable, 'isPaid')) {
            return $sequenceable->isPaid();
        }

        return Payment::whereMorphedTo('purchasable', $sequenceable)
            ->where('status', PaymentStatus::Succeeded)
            ->exists();
    }

    public function shouldSuppress(): ?string
    {
        // Postmark suppresses hard-bounced / spam-flagged addresses and 406s
        // every send, so queueing mail to them just manufactures failed jobs.
        if ($this->user?->email_bounced_at !== null) {
            return 'email_bounced';
        }

        if (in_array($this->sequence_type, self::WAIVER_NURTURE_TYPES, true)) {
            return $this->shouldSuppressWaiverNurture();
        }

        if ($this->sequence_type === 'registration_unfinished') {
            return $this->shouldSuppressUnfinishedRegistration();
        }

        if ($this->sequence_type === 'resale_started') {
            return $this->shouldSuppressResaleStarted();
        }

        if ($this->sequence_type === 'sales_tax_started') {
            return $this->shouldSuppressSalesTaxStarted();
        }

        if ($this->trigger_status) {
            return $this->shouldSuppressTriggered();
        }

        if ($this->isPaymentCompleted()) {
            return 'payment_completed';
        }

        if ($this->sequence_type === 'abandon_checkout' && $reason = static::lienCheckoutStopReason($this->sequenceable)) {
            return $reason;
        }

        $config = $this->config();
        $user = $this->user()->first();

        if ($user && $config['unsubscribe_category'] && EmailUnsubscribe::isUnsubscribed($user, $config['unsubscribe_category'])) {
            return 'unsubscribed';
        }

        if ($this->currentStep() === null) {
            return 'all_steps_sent';
        }

        return null;
    }

    /**
     * Suppression logic for sequences tied to a specific trigger status.
     */
    protected function shouldSuppressTriggered(): ?string
    {
        $sequenceable = $this->sequenceable;

        if (! $sequenceable) {
            return 'sequenceable_deleted';
        }

        if ($sequenceable instanceof LienFiling) {
            $currentStatus = $sequenceable->status;

            if (! $currentStatus->isWaitingOnCustomer() || $currentStatus->value !== $this->trigger_status) {
                return 'status_changed';
            }
        }

        if ($this->currentStep() === null) {
            return 'all_steps_sent';
        }

        return null;
    }

    /**
     * Stop rules for the lien waiver nurture emails. "Finish your waiver"
     * stops once the user has made one; the free-plan series stops when the
     * waiver is gone or voided, or the user now holds a Pro seat (every email
     * in it sells Pro or assumes the free plan).
     */
    protected function shouldSuppressWaiverNurture(): ?string
    {
        $user = $this->user;

        if ($user === null) {
            return 'user_deleted';
        }

        if (EmailUnsubscribe::isUnsubscribed($user, EmailUnsubscribe::CATEGORY_MARKETING)) {
            return 'unsubscribed';
        }

        if ($this->sequence_type === 'waiver_started') {
            // Deleted waivers count: they made one, so "finish your waiver" is wrong.
            $madeAWaiver = LienWaiver::query()
                ->withoutGlobalScope('business')
                ->withTrashed()
                ->where('created_by_user_id', $user->id)
                ->exists();

            if ($madeAWaiver) {
                return 'waiver_created';
            }
        } else {
            $waiver = $this->sequenceable;

            if (! $waiver instanceof LienWaiver) {
                return 'sequenceable_deleted';
            }

            if ($waiver->status === WaiverStatus::Voided) {
                return 'waiver_voided';
            }

            if ($this->business !== null && WaiverEntitlements::hasPaidAccess($this->business, $user)) {
                return 'subscribed';
            }
        }

        return $this->currentStep() === null ? 'all_steps_sent' : null;
    }

    /**
     * Stop rules for "finish the questions": the sequence lives while the
     * registration is paid and still open. Submitting it (the wizard locks
     * it) ends the series; so does a payment that went away or was refunded
     * (a refund leaves paid_at set on the application).
     */
    protected function shouldSuppressUnfinishedRegistration(): ?string
    {
        $application = $this->sequenceable;

        if (! $application instanceof FormApplication) {
            return 'sequenceable_deleted';
        }

        if ($application->isLocked()) {
            return 'submitted';
        }

        if (! $application->isPaid()) {
            return 'not_paid';
        }

        if ($application->isRefunded()) {
            return 'refunded';
        }

        return $this->currentStep() === null ? 'all_steps_sent' : null;
    }

    /**
     * Stop rules for the resale certificate follow-ups. Subscribing ends the
     * series. So does paying for a sales tax registration: with no permit
     * yet, the permit-approved email offers certificates once the number
     * arrives. Opting out of order reminders stops it too.
     */
    protected function shouldSuppressResaleStarted(): ?string
    {
        $user = $this->user;

        if ($user === null) {
            return 'user_deleted';
        }

        if (EmailUnsubscribe::isUnsubscribed($user, EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT)) {
            return 'unsubscribed';
        }

        $businesses = $user->businesses()->get();

        if ($businesses->contains(fn (Business $business) => $business->subscribed(config('resale_cert.subscription_type')))) {
            return 'subscribed';
        }

        $registrationPaid = FormApplication::query()
            ->where('form_type', 'sales_tax_permit')
            ->whereIn('business_id', $businesses->modelKeys())
            ->whereNotNull('paid_at')
            ->exists();

        if ($registrationPaid) {
            return 'registration_paid';
        }

        return $this->currentStep() === null ? 'all_steps_sent' : null;
    }

    /**
     * Stop rules for the sales tax follow-ups. Picking a state ends the
     * series: it creates the application and opens the order screen, whose
     * own reminders take over. Opting out of order reminders stops it too.
     */
    protected function shouldSuppressSalesTaxStarted(): ?string
    {
        $user = $this->user;

        if ($user === null) {
            return 'user_deleted';
        }

        if (EmailUnsubscribe::isUnsubscribed($user, EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT)) {
            return 'unsubscribed';
        }

        if (SalesTaxFollowUp::hasApplication($user)) {
            return 'order_started';
        }

        return $this->currentStep() === null ? 'all_steps_sent' : null;
    }

    /**
     * Start the "finish the questions" series for a paid, still-open sales
     * tax registration. Idempotent per application (the unique key), so a
     * second payment event does not restart it.
     */
    public static function startUnfinishedRegistrationFor(
        FormApplication $application,
        User $user,
        Business $business,
        string $resumeUrl
    ): ?self {
        if (! $application->isPaid() || $application->isLocked()) {
            return null;
        }

        $config = static::$sequenceConfig['registration_unfinished'];

        return static::firstOrCreate(
            [
                'sequence_type' => 'registration_unfinished',
                'sequenceable_type' => $application->getMorphClass(),
                'sequenceable_id' => $application->getKey(),
            ],
            [
                'user_id' => $user->getKey(),
                'business_id' => $business->getKey(),
                'customer_type' => static::detectCustomerType($business),
                'resume_url' => $resumeUrl,
                'next_send_at' => now()->addMinutes($config['delays'][0]),
            ]
        );
    }

    /**
     * Record current step as sent via SentEmail and schedule the next one.
     */
    public function advanceStep(int $step): void
    {
        $config = $this->config();
        $nextStep = $step + 1;

        if ($nextStep > $config['steps']) {
            $this->update([
                'completed_at' => now(),
                'next_send_at' => null,
            ]);

            return;
        }

        $delayMinutes = $config['delays'][$nextStep - 1] ?? $config['delays'][0];

        $this->update(['next_send_at' => now()->addMinutes($delayMinutes)]);
    }

    /**
     * Suppress this sequence with a reason.
     */
    public function suppress(string $reason): void
    {
        $this->update([
            'suppressed_at' => now(),
            'suppression_reason' => $reason,
            'next_send_at' => null,
        ]);
    }

    /**
     * Pause this sequence: email:process-sequences sends nothing while it is
     * paused. Unlike suppress(), it is not over and keeps its schedule.
     */
    public function pause(string $reason): void
    {
        $this->update([
            'paused_at' => now(),
            'pause_reason' => $reason,
        ]);
    }

    /**
     * Undo pause(). A step that fell due while paused goes out on the next
     * run.
     */
    public function resume(): void
    {
        $this->update([
            'paused_at' => null,
            'pause_reason' => null,
        ]);
    }

    /**
     * Why a lien "finish your order" email must not go out, or null. Covers
     * filing drafts (wizard, checkout) and the first project (onboarding).
     * The emails stop when the business already has a paid order being worked
     * on, paid in the last LIEN_ORDER_IN_PROGRESS_DAYS days, or when the draft
     * repeats a filing they paid for on the same job (same document, any age,
     * not since refunded or canceled). Also runs from the scheduler, with no
     * business in scope.
     */
    protected static function lienCheckoutStopReason(?Model $sequenceable): ?string
    {
        if (! $sequenceable instanceof LienFiling && ! $sequenceable instanceof LienProject) {
            return null;
        }

        $otherPaidFilings = fn () => LienFiling::withoutGlobalScope('business')
            ->where('business_id', $sequenceable->business_id)
            ->whereNotNull('paid_at')
            ->when($sequenceable instanceof LienFiling, fn ($query) => $query->whereKeyNot($sequenceable->getKey()));

        if ($sequenceable instanceof LienFiling) {
            // Refunded and canceled orders don't count: the wizard lets
            // those customers start over (FilingWizard::mount).
            $duplicate = $otherPaidFilings()
                ->where('project_id', $sequenceable->project_id)
                ->where('document_type_id', $sequenceable->document_type_id)
                ->whereNotIn('status', [FilingStatus::Refunded, FilingStatus::Canceled])
                ->exists();

            if ($duplicate) {
                return 'duplicate_of_paid_filing';
            }
        }

        $orderInProgress = $otherPaidFilings()
            ->whereIn('status', self::LIEN_ORDER_IN_PROGRESS_STATUSES)
            ->where('paid_at', '>=', now()->subDays(self::LIEN_ORDER_IN_PROGRESS_DAYS))
            ->exists();

        return $orderInProgress ? 'paid_filing_in_progress' : null;
    }

    /**
     * Determine customer type for a business.
     */
    public static function detectCustomerType(Business $business): string
    {
        $hasPaid = Payment::where('business_id', $business->id)
            ->where('status', PaymentStatus::Succeeded)
            ->exists();

        return $hasPaid ? 'returning' : 'new';
    }

    /**
     * Create or retrieve an email sequence for a given sequenceable entity.
     * Returns null if the sequenceable is already paid, or if it is a lien
     * order whose "finish your order" emails must not start (see
     * lienCheckoutStopReason).
     */
    public static function startFor(
        string $sequenceType,
        Model $sequenceable,
        User $user,
        Business $business,
        ?string $resumeUrl = null
    ): ?self {
        if (method_exists($sequenceable, 'isPaid') && $sequenceable->isPaid()) {
            return null;
        }

        if ($sequenceType === 'abandon_checkout' && static::lienCheckoutStopReason($sequenceable) !== null) {
            return null;
        }

        $config = static::$sequenceConfig[$sequenceType];

        return static::firstOrCreate(
            [
                'sequence_type' => $sequenceType,
                'sequenceable_type' => $sequenceable->getMorphClass(),
                'sequenceable_id' => $sequenceable->getKey(),
            ],
            [
                'user_id' => $user->getKey(),
                'business_id' => $business->getKey(),
                'customer_type' => static::detectCustomerType($business),
                'resume_url' => $resumeUrl,
                'next_send_at' => now()->addMinutes($config['delays'][0]),
            ]
        );
    }

    /**
     * Start (or restart) a filing action reminder sequence for the given filing.
     * Deletes any existing sequence + SentEmail records first to satisfy the unique constraint.
     */
    public static function startReminderFor(
        LienFiling $filing,
        FilingStatus $triggerStatus,
        User $user,
        Business $business
    ): self {
        static::deleteReminderFor($filing);

        $sequence = new static([
            'sequence_type' => 'filing_action_reminder',
            'sequenceable_type' => $filing->getMorphClass(),
            'sequenceable_id' => $filing->getKey(),
            'user_id' => $user->getKey(),
            'business_id' => $business->getKey(),
            'trigger_status' => $triggerStatus->value,
        ]);

        // config() knows the trigger status, so e-sign reminders start on their own schedule.
        $sequence->next_send_at = now()->addMinutes($sequence->config()['delays'][0]);
        $sequence->save();

        return $sequence;
    }

    /**
     * Suppress the active filing action reminder sequence for a filing.
     */
    public static function suppressReminderFor(LienFiling $filing): void
    {
        static::query()
            ->where('sequence_type', 'filing_action_reminder')
            ->where('sequenceable_type', $filing->getMorphClass())
            ->where('sequenceable_id', $filing->getKey())
            ->whereNull('suppressed_at')
            ->whereNull('completed_at')
            ->get()
            ->each(fn (self $seq) => $seq->suppress('status_changed'));
    }

    /**
     * Pause the running filing action reminder sequence for a filing (the
     * customer emailed us, EREG-132). A status change still suppresses it,
     * and startReminderFor() replaces it with a fresh one.
     */
    public static function pauseReminderFor(LienFiling $filing, string $reason): void
    {
        static::query()
            ->where('sequence_type', 'filing_action_reminder')
            ->where('sequenceable_type', $filing->getMorphClass())
            ->where('sequenceable_id', $filing->getKey())
            ->whereNull('suppressed_at')
            ->whereNull('completed_at')
            ->whereNull('paused_at')
            ->get()
            ->each(fn (self $seq) => $seq->pause($reason));
    }

    /**
     * Suppress every running sequence about this record, of any type.
     */
    public static function suppressAllFor(Model $sequenceable, string $reason): void
    {
        static::query()
            ->where('sequenceable_type', $sequenceable->getMorphClass())
            ->where('sequenceable_id', $sequenceable->getKey())
            ->whereNull('suppressed_at')
            ->whereNull('completed_at')
            ->get()
            ->each(fn (self $seq) => $seq->suppress($reason));
    }

    /**
     * Delete any existing filing action reminder sequence + its SentEmail records.
     */
    protected static function deleteReminderFor(LienFiling $filing): void
    {
        $existing = static::query()
            ->where('sequence_type', 'filing_action_reminder')
            ->where('sequenceable_type', $filing->getMorphClass())
            ->where('sequenceable_id', $filing->getKey())
            ->first();

        if (! $existing) {
            return;
        }

        SentEmail::where('emailable_type', $existing->getMorphClass())
            ->where('emailable_id', $existing->id)
            ->delete();

        $existing->delete();
    }
}
