<?php

namespace App\Console\Commands;

use App\Domains\Forms\Models\FormApplication;
use App\Enums\PaymentStatus;
use App\Mail\UnpaidRegistrationCheckIn;
use App\Models\EmailUnsubscribe;
use App\Models\SentEmail;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * A personal note from the owner to people who started a sales tax
 * registration, reached the payment step (a payment row exists for the
 * application) and never paid, asking what stopped them. Run by hand, not
 * scheduled: the owner reads the replies himself.
 *
 * Once per person, ever: the sent_emails row is keyed to the user and written
 * before the mail is queued. Anyone whose business has since paid for a
 * registration is skipped, as are test accounts, bounced addresses and people
 * who turned off order reminders or marketing.
 */
class SendUnpaidRegistrationCheckIns extends Command
{
    public const EMAIL_TYPE = 'unpaid_registration_check_in';

    protected $signature = 'email:unpaid-registration-check-in
        {--since= : Only registrations started on or after this date (Eastern), default 60 days ago}
        {--dry-run : List who would get the email without sending or logging anything}';

    protected $description = 'Email a personal note from Major to sales tax sign-ups who reached payment and did not pay';

    /** Leave a day for the regular order reminders and anyone still at the card form. */
    private const MIN_AGE_HOURS = 24;

    public function handle(): int
    {
        $since = $this->option('since')
            ? CarbonImmutable::parse($this->option('since'), config('app.display_timezone'))->startOfDay()->utc()
            : now()->subDays(60)->toImmutable();
        $dryRun = (bool) $this->option('dry-run');
        $sent = [];

        foreach ($this->unpaidApplications($since) as $application) {
            $user = $application->createdBy
                ?? $application->business?->users()->orderBy('users.id')->first();
            $label = "registration #{$application->id} (".implode(', ', (array) $application->selected_states).')';

            if (! $user) {
                $this->line("  Skipped {$label}: no one to email");

                continue;
            }

            if (isset($sent[$user->id]) || $this->alreadySent($user)) {
                continue;
            }

            $who = "user #{$user->id} ({$user->email})";

            if ($reason = $this->skipReason($user, $application)) {
                $this->line("  Skipped {$who}, {$reason}: {$label}");

                continue;
            }

            $states = array_values((array) $application->selected_states);

            if ($dryRun) {
                $this->line("  Would email {$who}: {$label}");
                $sent[$user->id] = true;

                continue;
            }

            $claimed = SentEmail::recordOrSkip(self::EMAIL_TYPE, $user, $user, function () use ($user, $states) {
                Mail::to($user)->queue(new UnpaidRegistrationCheckIn($user, $states));
            });

            if ($claimed) {
                $this->line("  Emailed {$who}: {$label}");
                $sent[$user->id] = true;
            }
        }

        $count = count($sent);

        $this->info($dryRun
            ? "Dry run: {$count} person(s) would get the check-in email."
            : "Emailed {$count} person(s).");

        return self::SUCCESS;
    }

    /**
     * Unpaid sales tax registrations started since $since (and at least a day
     * ago) that reached the card form, newest first.
     *
     * @return \Illuminate\Support\Collection<int, FormApplication>
     */
    private function unpaidApplications(CarbonImmutable $since)
    {
        return FormApplication::query()
            ->forList()
            ->where('form_type', 'sales_tax_permit')
            ->whereNull('paid_at')
            ->whereBetween('created_at', [$since, now()->subHours(self::MIN_AGE_HOURS)])
            ->whereHas('payments')
            ->whereDoesntHave('payments', fn (Builder $payments) => $payments
                ->whereIn('status', [PaymentStatus::Succeeded, PaymentStatus::Refunded]))
            ->with(['business', 'createdBy'])
            ->orderByDesc('id')
            ->get();
    }

    private function alreadySent(User $user): bool
    {
        return SentEmail::where('user_id', $user->id)->where('email_type', self::EMAIL_TYPE)->exists();
    }

    private function skipReason(User $user, FormApplication $application): ?string
    {
        $domain = Str::lower(Str::afterLast($user->email, '@'));

        return match (true) {
            in_array($domain, config('mail.blocked_recipient_domains', []), true) => 'test account',
            $user->email_bounced_at !== null => 'email bounced',
            EmailUnsubscribe::isUnsubscribed($user, EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT),
            EmailUnsubscribe::isUnsubscribed($user, EmailUnsubscribe::CATEGORY_MARKETING) => 'unsubscribed',
            $this->paidSince($user, $application) => 'paid for a registration since',
            default => null,
        };
    }

    /** Whether any of the person's businesses has a paid sales tax registration. */
    private function paidSince(User $user, FormApplication $application): bool
    {
        $businessIds = $user->businesses()->pluck('businesses.id')->push($application->business_id)->unique()->all();

        return FormApplication::query()
            ->whereIn('business_id', $businessIds)
            ->where('form_type', 'sales_tax_permit')
            ->whereNotNull('paid_at')
            ->exists();
    }
}
