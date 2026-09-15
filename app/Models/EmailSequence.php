<?php

namespace App\Models;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
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
    ];

    protected function casts(): array
    {
        return [
            'next_send_at' => 'datetime',
            'completed_at' => 'datetime',
            'suppressed_at' => 'datetime',
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
    ];

    /**
     * E-signature links expire 14 days after they're sent, so e-sign reminders
     * run on days 2, 5, 8, 11 and 13 instead; the day-13 one warns that the link
     * expires in 24 hours (see FilingActionReminder).
     */
    private const ESIGN_REMINDER_DELAYS = [2880, 4320, 4320, 4320, 2880];

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

        if ($this->trigger_status) {
            return $this->shouldSuppressTriggered();
        }

        if ($this->isPaymentCompleted()) {
            return 'payment_completed';
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
     * Returns null if the sequenceable is already paid.
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
