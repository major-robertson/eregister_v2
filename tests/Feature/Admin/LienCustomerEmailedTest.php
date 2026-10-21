<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Admin\Livewire\LienBoard;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Admin\Support\BoardCard;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Mail\LienAgentDigest;
use App\Models\EmailSequence;
use App\Models\SentEmail;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
| EREG-132: a customer's email (Front webhook) flags their filing for the
| agent: a "Customer emailed" badge with the Front link on the filing page
| and the board card, the card first in its column, red after a business
| day. A status change or "Still waiting" clears it; "Still waiting" also
| restarts the reminders. The status never changes by itself.
*/

beforeEach(function () {
    Mail::fake();
    Storage::fake('s3');

    // Thursday afternoon, Eastern.
    $this->travelTo(CarbonImmutable::parse('2026-10-22 15:00:00', 'America/New_York')->utc());

    $this->admin = User::factory()->create(['first_name' => 'Janice']);
    $this->admin->givePermissionTo(['lien.view', 'lien.update', 'lien.change_status']);

    $this->customer = User::factory()->create(['email' => 'dana@customer.test']);
});

if (! function_exists('emailedFiling')) {
    /**
     * A paid filing for its own named business, created by $customer. A
     * waiting status is reached through transitionTo, so reminders start.
     */
    function emailedFiling(string $businessName, FilingStatus $status, User $customer): LienFiling
    {
        $business = Business::factory()->create(['name' => $businessName]);
        $business->users()->attach($customer, ['role' => 'owner']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);

        $filing = LienFiling::factory()->forProject($project)->create([
            'status' => $status->isWaitingOnCustomer() ? FilingStatus::Paid : $status,
            'paid_at' => now()->subDays(3),
            'created_by_user_id' => $customer->id,
        ]);

        if ($status->isWaitingOnCustomer()) {
            $filing->transitionTo($status);
        }

        return $filing->fresh();
    }
}

if (! function_exists('emailedReminder')) {
    function emailedReminder(LienFiling $filing): ?EmailSequence
    {
        return EmailSequence::query()
            ->where('sequence_type', 'filing_action_reminder')
            ->where('sequenceable_type', $filing->getMorphClass())
            ->where('sequenceable_id', $filing->id)
            ->first();
    }
}

if (! function_exists('flagEmailed')) {
    function flagEmailed(LienFiling $filing, string $conversationId = 'cnv_abc123'): LienFiling
    {
        $filing->recordCustomerEmail($conversationId, 'https://app.frontapp.com/open/'.$conversationId, 'dana@customer.test');

        return $filing->fresh();
    }
}

describe('the badge', function () {
    it('shows on the filing page next to the status, with the Front link and Still waiting', function () {
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer));

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Customer emailed &middot; '.$filing->customer_emailed_at->diffForHumans(), false)
            ->assertSee('Open in Front')
            ->assertSee('href="https://app.frontapp.com/open/cnv_abc123"', false)
            ->assertSee('Still waiting')
            ->assertSee('to restart the reminders')
            // The activity log entry.
            ->assertSee('Customer emailed us')
            ->assertSee('from dana@customer.test');
    });

    it('is not on the filing page when the customer has not emailed', function () {
        $filing = emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer);

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSee('Customer emailed')
            ->assertDontSee('Still waiting');
    });

    it('shows without the Still waiting button to people who cannot change statuses', function () {
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer));

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('lien.view');
        $this->actingAs($viewer);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Customer emailed')
            ->assertSee('Open in Front')
            ->assertDontSee('Still waiting');

        Livewire::test(LienBoard::class)
            ->assertSee('Customer emailed')
            ->assertDontSee('Still waiting');
    });

    it('shows on the board card with the Front link and Still waiting, and counts as the answer', function () {
        $waiting = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer));
        $review = flagEmailed(emailedFiling('Review Roofing LLC', FilingStatus::NeedsReview, $this->customer));

        expect(BoardCard::all()->get($waiting->id)->customerAnswered())->toBeTrue()
            ->and(BoardCard::all()->get($waiting->id)->customerRepliedAt->equalTo($waiting->customer_emailed_at))->toBeTrue()
            ->and(BoardCard::all()->get($review->id)->customerAnswered())->toBeTrue();

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)
            ->assertSee('Customer emailed')
            ->assertSee('href="https://app.frontapp.com/open/cnv_abc123"', false)
            ->assertSee('Still waiting')
            ->assertSee('Answered')
            ->assertSee('Customer answered');
    });

    it('counts the newest of a page answer and an email as the reply', function () {
        $filing = emailedFiling('Dana Drywall LLC', FilingStatus::NeedsReview, $this->customer);
        $answer = $filing->events()->create([
            'business_id' => $filing->business_id,
            'event_type' => 'client_response',
            'payload_json' => ['text' => 'Attached.'],
        ]);
        $answer->forceFill(['created_at' => now()->subDay()])->save();

        expect($filing->fresh()->customerRepliedAt()->equalTo($answer->fresh()->created_at))->toBeTrue();

        $filing = flagEmailed($filing);

        expect($filing->customerRepliedAt()->equalTo($filing->customer_emailed_at))->toBeTrue();
    });

    it('puts the card first in its column while flagged', function () {
        $soon = emailedFiling('Soon Deadline LLC', FilingStatus::AwaitingClient, $this->customer);
        $later = emailedFiling('Later Deadline LLC', FilingStatus::AwaitingClient, $this->customer);

        foreach ([[$soon, now()->addDays(3)], [$later, now()->addDays(30)]] as [$filing, $due]) {
            $deadline = LienProjectDeadline::factory()->forProject($filing->project)->create(['due_date' => $due]);
            $filing->update(['project_deadline_id' => $deadline->id]);
        }

        $order = fn () => BoardCard::all()
            ->filter(fn (BoardCard $card) => $card->column === KanbanColumn::WaitingOnCustomer)
            ->map(fn (BoardCard $card) => $card->filing->id)
            ->values()
            ->all();

        expect($order())->toBe([$soon->id, $later->id]);

        flagEmailed($later);

        expect($order())->toBe([$later->id, $soon->id]);

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)->assertSeeInOrder(['Later Deadline LLC', 'Soon Deadline LLC']);
    });
});

describe('Still waiting', function () {
    it('clears the flag, logs the note and restarts the reminders from step 1, leaving the status', function () {
        $filing = emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer);

        // One reminder already went out before the customer emailed.
        $old = emailedReminder($filing);
        SentEmail::create([
            'email_type' => 'filing_action_reminder_step_1',
            'emailable_type' => $old->getMorphClass(),
            'emailable_id' => $old->id,
            'user_id' => $this->customer->id,
            'sent_at' => now(),
        ]);
        expect($old->currentStep())->toBe(2);

        $filing = flagEmailed($filing);
        expect(emailedReminder($filing)->paused_at)->not->toBeNull();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->set('stillWaitingNote', 'Asked again for the signed contract.')
            ->call('stillWaiting')
            ->assertHasNoErrors()
            ->assertSet('stillWaitingNote', '')
            ->assertSee('Marked still waiting. The reminders start again from the first one.')
            // The badge is gone; the activity log keeps the email and its link.
            ->assertDontSee('Customer emailed &middot;', false);

        $filing->refresh();

        expect($filing->status)->toBe(FilingStatus::AwaitingClient)
            ->and($filing->customer_emailed_at)->toBeNull()
            ->and($filing->customer_emailed_front_url)->toBeNull();

        $event = LienFilingEvent::query()->where('filing_id', $filing->id)->where('event_type', 'reply_dismissed')->sole();

        expect($event->payload_json)->toMatchArray([
            'note' => 'Asked again for the signed contract.',
            'front_url' => 'https://app.frontapp.com/open/cnv_abc123',
            'reminders_restarted' => true,
        ])->and($event->created_by)->toBe($this->admin->id);

        $new = emailedReminder($filing);

        expect(EmailSequence::find($old->id))->toBeNull()
            ->and($new->paused_at)->toBeNull()
            ->and($new->suppressed_at)->toBeNull()
            ->and($new->trigger_status)->toBe('awaiting_client')
            ->and($new->currentStep())->toBe(1)
            ->and($new->next_send_at->equalTo(now()->addMinutes(2880)))->toBeTrue();
    });

    it('works from the board with no note', function () {
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingEsign, $this->customer));
        $old = emailedReminder($filing);

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)
            ->call('stillWaiting', $filing->public_id)
            ->assertDontSee('Customer emailed');

        $filing->refresh();
        $event = LienFilingEvent::query()->where('filing_id', $filing->id)->where('event_type', 'reply_dismissed')->sole();

        expect($filing->status)->toBe(FilingStatus::AwaitingEsign)
            ->and($filing->customer_emailed_at)->toBeNull()
            ->and($event->payload_json['note'])->toBeNull()
            ->and(EmailSequence::find($old->id))->toBeNull()
            ->and(emailedReminder($filing)->paused_at)->toBeNull()
            ->and(emailedReminder($filing)->trigger_status)->toBe('awaiting_esign');
    });

    it('starts no reminders on a filing that is not waiting on the customer', function () {
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::NeedsReview, $this->customer));

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('stillWaiting')
            ->assertSee('Marked still waiting.');

        expect($filing->fresh()->customer_emailed_at)->toBeNull()
            ->and($filing->fresh()->status)->toBe(FilingStatus::NeedsReview)
            ->and(emailedReminder($filing))->toBeNull()
            ->and(LienFilingEvent::query()->where('filing_id', $filing->id)->where('event_type', 'reply_dismissed')->sole()->payload_json['reminders_restarted'])->toBeFalse();
    });

    it('does nothing when the filing is not flagged', function () {
        $filing = emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer);
        $sequence = emailedReminder($filing);

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)->call('stillWaiting', $filing->public_id);

        expect(LienFilingEvent::query()->where('event_type', 'reply_dismissed')->exists())->toBeFalse()
            ->and(EmailSequence::find($sequence->id))->not->toBeNull();
    });

    it('is for people who can change statuses', function () {
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer));

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('lien.view');
        $this->actingAs($viewer);

        Livewire::test(LienBoard::class)->call('stillWaiting', $filing->public_id)->assertForbidden();
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])->call('stillWaiting')->assertForbidden();

        expect($filing->fresh()->customer_emailed_at)->not->toBeNull()
            ->and(emailedReminder($filing)->paused_at)->not->toBeNull();
    });

    it('keeps the note to one line', function () {
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer));

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->set('stillWaitingNote', str_repeat('a', 256))
            ->call('stillWaiting')
            ->assertHasErrors(['stillWaitingNote' => 'max']);

        expect($filing->fresh()->customer_emailed_at)->not->toBeNull();
    });
});

describe('a status change', function () {
    it('clears the flag, and the paused reminders are suppressed as usual', function () {
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer));

        $this->actingAs($this->admin);
        $filing->transitionTo(FilingStatus::NeedsReview);

        $filing->refresh();
        $sequence = emailedReminder($filing);

        expect($filing->customer_emailed_at)->toBeNull()
            ->and($filing->customer_emailed_front_url)->toBeNull()
            ->and($sequence->suppressed_at)->not->toBeNull()
            ->and($sequence->suppression_reason)->toBe('status_changed')
            ->and($sequence->paused_at)->not->toBeNull();
    });

    it('from the filing page clears the badge', function () {
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::NeedsReview, $this->customer));

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->set('newStatus', FilingStatus::ReadyToFile->value)
            ->call('updateStatus')
            ->assertHasNoErrors()
            // The badge is gone; the activity log keeps the email and its link.
            ->assertDontSee('Customer emailed &middot;', false);

        expect($filing->fresh()->customer_emailed_at)->toBeNull();
    });
});

describe('the red alert and the digest', function () {
    it('turns a flagged card red after a business day, even with a note since', function () {
        // Wednesday 4 PM Eastern; now is Thursday 3 PM.
        $this->travelTo(CarbonImmutable::parse('2026-10-21 16:00:00', 'America/New_York')->utc());
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer));
        $this->travelTo(CarbonImmutable::parse('2026-10-22 15:00:00', 'America/New_York')->utc());

        $filing->events()->create([
            'business_id' => $filing->business_id,
            'event_type' => 'note_added',
            'payload_json' => ['comment' => 'Reading it.'],
            'created_by' => $this->admin->id,
        ]);

        expect(BoardCard::all()->get($filing->id)->alert)->toBe("Customer's email waiting 1 business day");

        config(['lien.notifications.ops_email' => 'liens@example.test']);
        $this->artisan('lien:agent-digest')->assertSuccessful();

        Mail::assertQueued(LienAgentDigest::class, fn (LienAgentDigest $mail) => collect($mail->cards)->contains(
            fn (array $card) => $card['business'] === 'Dana Drywall LLC' && $card['alert'] === "Customer's email waiting 1 business day"
        ));
    });

    it('does not turn a fresh email red', function () {
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::AwaitingClient, $this->customer));

        expect(BoardCard::all()->get($filing->id)->alert)->toBeNull();
    });

    it('counts Still waiting as our action', function () {
        $this->travelTo(CarbonImmutable::parse('2026-10-19 10:00:00', 'America/New_York')->utc());
        $filing = flagEmailed(emailedFiling('Dana Drywall LLC', FilingStatus::NeedsReview, $this->customer));
        $this->travelTo(CarbonImmutable::parse('2026-10-22 15:00:00', 'America/New_York')->utc());

        expect(BoardCard::all()->get($filing->id)->alert)->toBe("Customer's email waiting 3 business days");

        $this->actingAs($this->admin);
        Livewire::test(LienBoard::class)->call('stillWaiting', $filing->public_id);

        expect(BoardCard::all()->get($filing->id)->alert)->toBeNull()
            ->and(BoardCard::all()->get($filing->id)->lastActionAt->equalTo(now()))->toBeTrue();
    });
});
