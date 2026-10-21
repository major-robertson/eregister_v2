<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Mail\LienAgentDigest;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Mail;

/*
| EREG-133: lien:agent-digest emails lien ops the red cards on the lien board
| on weekday mornings, and nothing when no card is red.
*/

beforeEach(function () {
    Mail::fake();

    // Thursday afternoon, Eastern.
    $this->travelTo(CarbonImmutable::parse('2026-10-22 15:00:00', 'America/New_York')->utc());

    config(['lien.notifications.ops_email' => 'liens@example.test']);
});

if (! function_exists('digestFiling')) {
    function digestFiling(string $businessName, FilingStatus $status, CarbonImmutable $paidAt): LienFiling
    {
        $business = Business::factory()->create(['name' => $businessName]);
        $project = LienProject::factory()->create(['business_id' => $business->id]);

        return LienFiling::factory()->forProject($project)->create([
            'status' => $status,
            'paid_at' => $paidAt,
            'document_type_id' => LienDocumentType::where('slug', 'prelim_notice')->value('id'),
        ]);
    }
}

it('emails lien ops the red cards with business, document, column, age and link', function () {
    $stale = digestFiling('Stale Drywall LLC', FilingStatus::Paid, now()->subDays(10));
    digestFiling('Fresh Roofing LLC', FilingStatus::Paid, now()->subHour());

    $this->artisan('lien:agent-digest')
        ->expectsOutputToContain('Emailed 1 red card(s) to liens@example.test.')
        ->assertSuccessful();

    Mail::assertQueued(LienAgentDigest::class, function (LienAgentDigest $mail) use ($stale) {
        expect($mail->cards)->toBe([[
            'business' => 'Stale Drywall LLC',
            'document' => 'Preliminary Notice',
            'column' => 'New',
            'alert' => 'No action for 8 business days',
            'url' => route('admin.liens.show', $stale->public_id),
        ]]);

        $html = $mail->render();

        expect($html)->toContain('Stale Drywall LLC')
            ->toContain('Preliminary Notice')
            ->toContain('No action for 8 business days')
            ->toContain(route('admin.liens.show', $stale->public_id))
            ->not->toContain('Fresh Roofing LLC');

        return $mail->hasTo('liens@example.test')
            && $mail->hasReplyTo(config('mail.support_address'))
            && $mail->hasSubject('Lien board: 1 filing waiting on us');
    });
});

it('sends nothing when no card is red', function () {
    digestFiling('Fresh Roofing LLC', FilingStatus::Paid, now()->subHour());
    digestFiling('Parked Co', FilingStatus::Hold, now()->subMonth());

    $this->artisan('lien:agent-digest')
        ->expectsOutputToContain('No red cards on the lien board. Nothing to send.')
        ->assertSuccessful();

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
});

it('prints the list on a dry run and sends nothing', function () {
    digestFiling('Stale Drywall LLC', FilingStatus::Paid, now()->subDays(10));

    $this->artisan('lien:agent-digest', ['--dry-run' => true])
        ->expectsOutputToContain('Stale Drywall LLC | Preliminary Notice | New | No action for 8 business days')
        ->expectsOutputToContain('Dry run: 1 red card(s) would be emailed to liens@example.test.')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

it('sends nothing without an ops inbox', function () {
    config(['lien.notifications.ops_email' => null]);
    digestFiling('Stale Drywall LLC', FilingStatus::Paid, now()->subDays(10));

    $this->artisan('lien:agent-digest')
        ->expectsOutputToContain('No lien ops inbox is set')
        ->assertSuccessful();

    Mail::assertNothingQueued();
});

it('runs on weekday mornings at 8 Eastern', function () {
    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => str_contains((string) $event->command, 'lien:agent-digest'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 8 * * 1-5')
        ->and($event->timezone)->toBe('America/New_York');
});
