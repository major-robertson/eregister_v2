<?php

namespace App\Console\Commands;

use App\Domains\Business\Models\Business;
use App\Mail\WebsitesIntro;
use App\Mail\WebsitesIntroReminder;
use App\Models\EmailUnsubscribe;
use App\Models\SentEmail;
use App\Models\User;
use App\Models\WebsiteInvitation;
use App\Support\HappyWebsites;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;

/**
 * Invites customers to Happy Websites by email (a free site, the first month
 * free, a spot held through a date) and sends the one reminder. Runs on
 * weekday mornings and sends nothing until happy_websites.intro.enabled is on.
 *
 * Each run does two things:
 *
 * 1. Reminders, 3 days before the date, to customers who have not answered.
 *    A reminder that missed its day still goes out while the date is at least
 *    a day away, and never on the date itself or after it.
 * 2. New invitations, up to the daily cap, newest sign-ups first. A new
 *    customer hears from us about two weeks after signing up, and the
 *    existing ones follow a few a day.
 *
 * Once per person, ever: the website_invitations row is unique per user and
 * is written before the mail is queued, so an overlapping run can't invite
 * the same person twice.
 */
class SendWebsitesIntro extends Command
{
    protected $signature = 'email:send-websites-intro
        {--dry-run : List who would get an email without sending or saving anything}
        {--test-to= : Send both emails to this account\'s address as a sample, without inviting anyone}';

    protected $description = 'Invite customers to Happy Websites by email and send the reminder 3 days before their date';

    /** How long the --test-to sample waits between the invitation and the reminder. */
    private const SAMPLE_GAP_SECONDS = 30;

    public function handle(): int
    {
        if (filled($this->option('test-to'))) {
            return $this->sendSample((string) $this->option('test-to'));
        }

        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! config('happy_websites.intro.enabled')) {
            $this->line('The Happy Websites intro emails are off (HAPPY_WEBSITES_INTRO_ENABLED). Nothing sent. Use --dry-run to see who would get one.');

            return self::SUCCESS;
        }

        if (! $dryRun && ! $this->hasPostalAddress()) {
            return self::FAILURE;
        }

        $reminded = $this->sendReminders($dryRun);
        $invited = $this->sendInvitations($dryRun);

        $this->info($dryRun
            ? "Dry run: {$invited} invitation(s) and {$reminded} reminder(s) would go out."
            : "Sent {$invited} invitation(s) and {$reminded} reminder(s).");

        if ($dryRun) {
            $this->line('Customers who could still be invited, in all: '.$this->eligible()->count());
        }

        return self::SUCCESS;
    }

    private function sendReminders(bool $dryRun): int
    {
        $today = CarbonImmutable::now(config('app.display_timezone'))->startOfDay();
        $sent = 0;

        $due = WebsiteInvitation::query()
            ->whereNull('reminded_at')
            ->whereNull('replied_at')
            ->whereBetween('deadline_on', [$today->addDay()->toDateString(), $today->addDays(3)->toDateString()])
            ->with(['user', 'business'])
            ->orderBy('id')
            ->get();

        foreach ($due as $invitation) {
            $user = $invitation->user;

            if ($user === null) {
                continue;
            }

            $who = "user #{$user->id} ({$user->email})";
            $reason = $this->skipReason($user)
                ?? (HappyWebsites::requestedBy($user) ? 'asked for a mockup in the portal' : null);

            if ($reason !== null) {
                $this->line("  No reminder for {$who}, {$reason}");

                continue;
            }

            if ($dryRun) {
                $this->line("  Would remind {$who}: spot held through {$invitation->deadlineForHumans()}");
                $sent++;

                continue;
            }

            // Claim it first, so an overlapping run can't send a second reminder.
            $claimed = WebsiteInvitation::query()
                ->whereKey($invitation->getKey())
                ->whereNull('reminded_at')
                ->update(['reminded_at' => now()]);

            if ($claimed === 1) {
                Mail::to($user)->queue(new WebsitesIntroReminder($invitation));
                $this->line("  Reminded {$who}: spot held through {$invitation->deadlineForHumans()}");
                $sent++;
            }
        }

        return $sent;
    }

    private function sendInvitations(bool $dryRun): int
    {
        $intro = config('happy_websites.intro');
        $deadline = WebsiteInvitation::deadlineFor(now());
        $sent = 0;

        $users = $this->eligible()
            ->whereNotIn('id', SentEmail::query()->select('user_id')->where('created_at', '>=', now()->subDays($intro['quiet_days'])))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(max(0, (int) $intro['daily_cap']))
            ->get();

        foreach ($users as $user) {
            $business = $user->businesses()->orderBy('businesses.id')->first();
            $who = "user #{$user->id} ({$user->email})";
            // The names as the email will print them, so a dry run shows what a customer reads.
            $what = '"Hi '.(WebsitesIntro::firstName($user) ?? 'there').'", '
                .WebsitesIntro::businessName($business).', spot held through '.$deadline->format('l, F j');

            if ($dryRun) {
                $this->line("  Would invite {$who}: {$what}");
                $sent++;

                continue;
            }

            try {
                $invitation = WebsiteInvitation::create([
                    'user_id' => $user->id,
                    'business_id' => $business?->id,
                    'message_id' => $this->newMessageId($user),
                    'invited_at' => now(),
                    'deadline_on' => $deadline,
                ]);
            } catch (UniqueConstraintViolationException) {
                continue;
            }

            Mail::to($user)->queue(new WebsitesIntro($invitation));
            $this->line("  Invited {$who}: {$what}");
            $sent++;
        }

        return $sent;
    }

    /**
     * Everyone who could still be invited: a customer with a business, signed
     * up between delay_days and max_age_months ago, whose email works and who
     * has not opted out, been invited already, or asked for a mockup in the
     * portal (that makes them a lead already). Staff and test accounts never.
     */
    private function eligible(): Builder
    {
        $intro = config('happy_websites.intro');

        return User::query()
            ->whereHas('businesses')
            ->whereDoesntHave('roles')
            ->whereNull('email_bounced_at')
            ->whereNull('unsubscribed_from_all_emails_at')
            ->where('created_at', '<=', now()->subDays($intro['delay_days']))
            ->where('created_at', '>=', now()->subMonths($intro['max_age_months']))
            ->whereNotIn('id', WebsiteInvitation::query()->select('user_id'))
            ->whereNotIn('id', EmailUnsubscribe::query()->select('user_id')->where('category', EmailUnsubscribe::CATEGORY_MARKETING))
            ->whereNotIn('id', SentEmail::query()->select('user_id')->where('email_type', HappyWebsites::REQUEST_EMAIL_TYPE))
            ->where(function (Builder $query): void {
                foreach (config('mail.blocked_recipient_domains', []) as $domain) {
                    $query->where('email', 'not like', '%@'.$domain);
                }
            });
    }

    /** Why someone invited earlier no longer gets the reminder. */
    private function skipReason(User $user): ?string
    {
        $domain = Str::lower(Str::afterLast($user->email, '@'));

        return match (true) {
            in_array($domain, config('mail.blocked_recipient_domains', []), true) => 'test account',
            $user->email_bounced_at !== null => 'email bounced',
            EmailUnsubscribe::isUnsubscribed($user, EmailUnsubscribe::CATEGORY_MARKETING) => 'unsubscribed',
            default => null,
        };
    }

    /**
     * Both emails, sent right now to one account's address, for a look in a
     * real inbox (the wording, the footer, the reminder landing in the same
     * thread). Nothing is saved and nobody is invited.
     */
    private function sendSample(string $address): int
    {
        $user = User::query()->where('email', $address)->first();

        if ($user === null) {
            $this->error("No account uses {$address}. Use the address of an eRegister account.");

            return self::FAILURE;
        }

        if (! $this->hasPostalAddress()) {
            return self::FAILURE;
        }

        $business = $user->businesses()->orderBy('businesses.id')->first() ?? new Business(['name' => 'Smith Roofing']);

        $invitation = new WebsiteInvitation([
            'message_id' => $this->newMessageId($user),
            'invited_at' => now(),
            'deadline_on' => WebsiteInvitation::deadlineFor(now()),
        ]);
        $invitation->setRelation('user', $user);
        $invitation->setRelation('business', $business);

        Mail::to($user)->sendNow(new WebsitesIntro($invitation));

        // The reminder is a reply to the first email. Sent in the same second,
        // a mailbox can file it before the email it answers has arrived, and
        // then shows the two as separate conversations. Real reminders follow
        // days later, so give the sample a head start too.
        $this->line("Sent the invitation to {$address}. Waiting ".self::SAMPLE_GAP_SECONDS.' seconds before the reminder, so the first email is in the inbox when it arrives.');
        Sleep::for(self::SAMPLE_GAP_SECONDS)->seconds();

        Mail::to($user)->sendNow(new WebsitesIntroReminder($invitation));

        $this->info("Sent the invitation and the reminder to {$address} as a sample. Nobody was invited.");

        return self::SUCCESS;
    }

    /** Promotional email must carry a postal address. */
    private function hasPostalAddress(): bool
    {
        if (filled(config('mail.postal_address'))) {
            return true;
        }

        $this->error('MAIL_POSTAL_ADDRESS is not set. Promotional email must carry a postal address, so nothing was sent.');

        return false;
    }

    /** "websites-intro.123.k2j4h5g6f7d8s9a0@eregister.com": the first email's Message-ID. */
    private function newMessageId(User $user): string
    {
        return 'websites-intro.'.$user->id.'.'.Str::lower(Str::random(16)).'@'.Str::afterLast((string) config('mail.from.address'), '@');
    }
}
