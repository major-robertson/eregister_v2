<?php

namespace App\Console\Commands;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Mail\ReviewRequest;
use App\Models\EmailUnsubscribe;
use App\Models\Payment;
use App\Models\SentEmail;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Asks a customer for a Google review 2 days after their lien is recorded or
 * a state approves their sales tax registration. Runs every morning.
 *
 * Once per person, ever: the sent_emails row is keyed to the user and written
 * before the mail is queued, so a second order, the other trigger or an
 * overlapping run can never ask the same person twice.
 *
 * Never for past clients: anyone whose business paid for anything before
 * review_requests.starts_at is skipped for good (the owner reaches them
 * himself), and only liens recorded and states approved since then count.
 * Nothing older than LOOKBACK_DAYS is asked about either, so a stretch of
 * downtime can't end in a burst of stale requests.
 */
class SendReviewRequests extends Command
{
    public const EMAIL_TYPE = 'review_request';

    protected $signature = 'email:send-review-requests
        {--dry-run : List who would be asked without sending or logging anything}';

    protected $description = 'Ask customers for a Google review 2 days after a lien is recorded or a sales tax state is approved';

    private const LOOKBACK_DAYS = 14;

    /** Recorded, or moved on from there without a problem. */
    private const LIEN_STATUSES = [
        FilingStatus::Recorded,
        FilingStatus::Mailed,
        FilingStatus::Complete,
    ];

    public function handle(): int
    {
        $startsAt = CarbonImmutable::parse(config('review_requests.starts_at'), config('app.display_timezone'))->utc();
        $until = now()->subDays((int) config('review_requests.delay_days', 2));
        $from = $startsAt->max(now()->subDays(self::LOOKBACK_DAYS));
        $dryRun = (bool) $this->option('dry-run');
        $asked = [];

        foreach ($this->milestones($from, $until) as $milestone) {
            $user = $milestone['user'];

            if (! $user) {
                $this->line("  Skipped: {$milestone['label']}, no one to ask");

                continue;
            }

            if (isset($asked[$user->id]) || $this->alreadyAsked($user)) {
                continue;
            }

            $who = "user #{$user->id} ({$user->email})";

            if ($reason = $this->skipReason($user, $milestone['business_id'], $startsAt)) {
                $this->line("  Skipped {$who}, {$reason}: {$milestone['label']}");

                continue;
            }

            if ($dryRun) {
                $this->line("  Would ask {$who}: {$milestone['label']}");
                $asked[$user->id] = true;

                continue;
            }

            $claimed = SentEmail::recordOrSkip(self::EMAIL_TYPE, $user, $user, function () use ($user) {
                Mail::to($user)->queue(new ReviewRequest($user));
            });

            if ($claimed) {
                $this->line("  Asked {$who}: {$milestone['label']}");
                $asked[$user->id] = true;
            }
        }

        $count = count($asked);

        $this->info($dryRun
            ? "Dry run: {$count} customer(s) would be asked for a review."
            : "Asked {$count} customer(s) for a review.");

        return self::SUCCESS;
    }

    /**
     * Liens recorded and sales tax states approved between $from and $until,
     * oldest first, each with the person who placed the order.
     *
     * @return Collection<int, array{label: string, business_id: int, user: ?User, at: CarbonImmutable}>
     */
    private function milestones(CarbonImmutable $from, CarbonImmutable $until): Collection
    {
        $liens = LienFiling::withoutGlobalScope('business')
            ->whereIn('status', self::LIEN_STATUSES)
            ->whereBetween('recorded_at', [$from, $until])
            ->with(['business', 'createdBy'])
            ->get()
            ->map(fn (LienFiling $filing) => [
                'label' => "lien filing {$filing->public_id} recorded {$this->eastern($filing->recorded_at)}",
                'business_id' => (int) $filing->business_id,
                'user' => $filing->createdBy ?? $this->firstUserOf($filing->business),
                'at' => $filing->recorded_at,
            ]);

        // Approved is final, so the status timestamp is when the state approved.
        $approvals = FormApplicationState::query()
            ->where('current_admin_status', FormApplicationStateAdminStatus::Approved)
            ->whereBetween('current_admin_status_changed_at', [$from, $until])
            ->whereHas('application', fn (Builder $application) => $application
                ->where('form_type', 'sales_tax_permit')
                ->notRefunded())
            ->with(['application' => fn ($application) => $application->forList()->with(['business', 'createdBy'])])
            ->get()
            ->map(fn (FormApplicationState $state) => [
                'label' => "{$state->state_code} sales tax registration approved {$this->eastern($state->current_admin_status_changed_at)}",
                'business_id' => (int) $state->application->business_id,
                'user' => $state->application->createdBy ?? $this->firstUserOf($state->application->business),
                'at' => $state->current_admin_status_changed_at,
            ]);

        return $liens->concat($approvals)->sortBy('at')->values();
    }

    private function alreadyAsked(User $user): bool
    {
        return SentEmail::where('user_id', $user->id)->where('email_type', self::EMAIL_TYPE)->exists();
    }

    private function skipReason(User $user, int $businessId, CarbonImmutable $startsAt): ?string
    {
        $domain = Str::lower(Str::afterLast($user->email, '@'));

        return match (true) {
            in_array($domain, config('mail.blocked_recipient_domains', []), true) => 'test account',
            $user->email_bounced_at !== null => 'email bounced',
            EmailUnsubscribe::isUnsubscribed($user, EmailUnsubscribe::CATEGORY_MARKETING) => 'unsubscribed',
            $this->isPastClient($user, $businessId, $startsAt) => 'past client',
            default => null,
        };
    }

    /**
     * Whether the order's business, or any other business the person belongs
     * to, paid for anything before the start date. Filings and applications
     * are checked as well as payments because older orders have no payment row.
     */
    private function isPastClient(User $user, int $businessId, CarbonImmutable $startsAt): bool
    {
        $businessIds = $user->businesses()->pluck('businesses.id')->push($businessId)->unique()->all();

        return Payment::whereIn('business_id', $businessIds)->where('paid_at', '<', $startsAt)->exists()
            || LienFiling::withoutGlobalScope('business')->withTrashed()->whereIn('business_id', $businessIds)->where('paid_at', '<', $startsAt)->exists()
            || FormApplication::whereIn('business_id', $businessIds)->where('paid_at', '<', $startsAt)->exists();
    }

    private function firstUserOf(?Business $business): ?User
    {
        return $business?->users()->orderBy('users.id')->first();
    }

    private function eastern(CarbonInterface $time): string
    {
        return $time->eastern()->format('M j, g:ia').' ET';
    }
}
