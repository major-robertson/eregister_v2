<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Admin\Livewire\LienBoard;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Admin\Support\BoardCard;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/*
| EREG-133: New means untouched; In Progress and Waiting on customer columns;
| request, answer and hold details on the cards; red ages; deadline order.
*/

beforeEach(function () {
    Mail::fake();
    Storage::fake('s3');

    // Thursday afternoon, Eastern.
    $this->travelTo(CarbonImmutable::parse('2026-10-22 15:00:00', 'America/New_York')->utc());

    $this->admin = User::factory()->create(['first_name' => 'Janice']);
    $this->admin->givePermissionTo(['lien.view', 'lien.update', 'lien.change_status']);

    $this->customer = User::factory()->create();
});

if (! function_exists('boardFiling')) {
    /** A paid filing for its own named business. */
    function boardFiling(string $businessName, FilingStatus $status, array $attributes = []): LienFiling
    {
        $business = Business::factory()->create(['name' => $businessName]);
        $project = LienProject::factory()->create(['business_id' => $business->id]);

        return LienFiling::factory()->forProject($project)->create(array_merge([
            'status' => $status,
            'paid_at' => now(),
        ], $attributes));
    }
}

if (! function_exists('boardEvent')) {
    /** A filing event at a given time. */
    function boardEvent(LienFiling $filing, string $type, CarbonInterface $at, ?User $by = null, array $payload = []): LienFilingEvent
    {
        $event = $filing->events()->create([
            'business_id' => $filing->business_id,
            'event_type' => $type,
            'payload_json' => $payload,
            'created_by' => $by?->id,
        ]);

        $event->forceFill(['created_at' => $at, 'updated_at' => $at])->save();

        return $event;
    }
}

if (! function_exists('boardCardFor')) {
    function boardCardFor(LienFiling $filing): BoardCard
    {
        return BoardCard::all()->get($filing->id);
    }
}

if (! function_exists('boardEastern')) {
    function boardEastern(string $time): CarbonImmutable
    {
        return CarbonImmutable::parse($time, 'America/New_York')->utc();
    }
}

describe('columns', function () {
    it('maps every status the board shows to its column', function (FilingStatus $status, KanbanColumn $column) {
        $filing = LienFiling::factory()->make(['status' => $status]);

        expect(KanbanColumn::forFiling($filing))->toBe($column)
            ->and(KanbanColumn::tryForStatus($status))->toBe($column);
    })->with([
        'paid' => [FilingStatus::Paid, KanbanColumn::New],
        'in fulfillment' => [FilingStatus::InFulfillment, KanbanColumn::InProgress],
        'awaiting client' => [FilingStatus::AwaitingClient, KanbanColumn::WaitingOnCustomer],
        'awaiting e-signature' => [FilingStatus::AwaitingEsign, KanbanColumn::WaitingOnCustomer],
        'awaiting notary' => [FilingStatus::AwaitingNotary, KanbanColumn::WaitingOnCustomer],
        'needs review' => [FilingStatus::NeedsReview, KanbanColumn::NeedsReview],
        'ready to file' => [FilingStatus::ReadyToFile, KanbanColumn::ReadyToFile],
        'waiting on next step' => [FilingStatus::WaitingOnNextStep, KanbanColumn::WaitingOnNextStep],
        'hold' => [FilingStatus::Hold, KanbanColumn::Hold],
        'submitted for recording' => [FilingStatus::SubmittedForRecording, KanbanColumn::SubmittedForRecording],
        'recorded' => [FilingStatus::Recorded, KanbanColumn::Recorded],
        'mailed' => [FilingStatus::Mailed, KanbanColumn::Mailed],
    ]);

    it('gives the statuses the board leaves out no column and refuses to guess one', function (FilingStatus $status) {
        $filing = LienFiling::factory()->make(['status' => $status]);

        expect(KanbanColumn::tryForFiling($filing))->toBeNull()
            ->and(fn () => KanbanColumn::forFiling($filing))->toThrow(LogicException::class, "Filing status {$status->value} has no board column.");
    })->with([
        'draft' => [FilingStatus::Draft],
        'awaiting payment' => [FilingStatus::AwaitingPayment],
        'complete' => [FilingStatus::Complete],
        'canceled' => [FilingStatus::Canceled],
        'refunded' => [FilingStatus::Refunded],
    ]);

    it('decides every status explicitly', function () {
        // A new FilingStatus without an arm throws UnhandledMatchError here.
        foreach (FilingStatus::cases() as $status) {
            KanbanColumn::tryForStatus($status);
        }

        expect(KanbanColumn::offBoardStatuses())->toBe([
            FilingStatus::Draft,
            FilingStatus::AwaitingPayment,
            FilingStatus::Complete,
            FilingStatus::Canceled,
            FilingStatus::Refunded,
        ]);
    });

    it('lays the columns out left to right in working order', function () {
        expect(array_map(fn (KanbanColumn $column) => $column->label(), KanbanColumn::cases()))->toBe([
            'New',
            'In Progress',
            'Waiting on customer',
            'Needs Review',
            'Ready to File',
            'Waiting on Next Step',
            'Hold',
            'Submitted for Recording',
            'Recorded',
            'Mailed to Parties',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)
            ->assertSeeInOrder(['New', 'In Progress', 'Waiting on customer', 'Needs Review', 'Ready to File']);
    });

    it('keeps an untouched full-service order in New, and moves it to In Progress once we act', function () {
        $filing = boardFiling('Untouched Co', FilingStatus::InFulfillment, ['service_level' => ServiceLevel::FullService]);

        // The payment flow logs these itself (customer or webhook, not us).
        boardEvent($filing, 'status_changed', now(), $this->customer, ['from' => 'awaiting_payment', 'to' => 'paid', 'meta' => []]);
        boardEvent($filing, 'status_changed', now(), null, ['from' => 'paid', 'to' => 'in_fulfillment', 'meta' => []]);

        expect(boardCardFor($filing)->column)->toBe(KanbanColumn::New);

        boardEvent($filing, 'note_added', now(), $this->admin, ['comment' => 'Pulling the deed.']);

        expect(boardCardFor($filing)->column)->toBe(KanbanColumn::InProgress);
    });

    it('shows New in the filing page header for an untouched full-service order, like the board', function () {
        $filing = boardFiling('Untouched Co', FilingStatus::InFulfillment, ['service_level' => ServiceLevel::FullService]);
        boardEvent($filing, 'status_changed', now(), null, ['from' => 'paid', 'to' => 'in_fulfillment', 'meta' => []]);

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertViewHas('kanbanColumn', KanbanColumn::New);
    });

    it('shows In Progress in the filing page header once we have acted on the order', function () {
        $filing = boardFiling('Worked Co', FilingStatus::InFulfillment, ['service_level' => ServiceLevel::FullService]);
        boardEvent($filing, 'status_changed', now()->subHour(), null, ['from' => 'paid', 'to' => 'in_fulfillment', 'meta' => []]);
        boardEvent($filing, 'note_added', now(), $this->admin, ['comment' => 'Pulling the deed.']);

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertViewHas('kanbanColumn', KanbanColumn::InProgress)
            // The page's own activity log still lists the note.
            ->assertSee('Pulling the deed.');
    });

    it('still shows the filing page header for a status the board leaves out', function () {
        $filing = boardFiling('Finished Co', FilingStatus::Complete, ['completed_at' => now()]);

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertOk()
            ->assertSee('Complete');
    });
});

describe('the Start button', function () {
    it('moves a Paid card to In Fulfillment with a note, and the card to In Progress', function () {
        $filing = boardFiling('Start Co', FilingStatus::Paid);

        $this->actingAs($this->admin);

        $component = Livewire::test(LienBoard::class)
            ->assertSeeHtml("start('{$filing->public_id}')")
            ->call('start', $filing->public_id);

        $filing->refresh();
        $event = $filing->events()->where('event_type', 'status_changed')->latest('id')->first();

        expect($filing->status)->toBe(FilingStatus::InFulfillment)
            ->and($event->payload_json['to'])->toBe('in_fulfillment')
            ->and($event->meta('note'))->toBe('Started from the board')
            ->and($event->created_by)->toBe($this->admin->id)
            ->and($component->instance()->getFilings()->get('in_progress')->pluck('id')->all())->toBe([$filing->id]);

        $component->assertDontSeeHtml("start('{$filing->public_id}')");
    });

    it('starts an untouched full-service order with a note, leaving its status alone', function () {
        $filing = boardFiling('Full Service Co', FilingStatus::InFulfillment, ['service_level' => ServiceLevel::FullService]);

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)->call('start', $filing->public_id);

        expect($filing->fresh()->status)->toBe(FilingStatus::InFulfillment)
            ->and($filing->events()->where('event_type', 'note_added')->first()->payload_json)->toBe(['comment' => 'Started from the board'])
            ->and(boardCardFor($filing)->column)->toBe(KanbanColumn::InProgress);
    });

    it('is for people who can change statuses', function () {
        $filing = boardFiling('Viewer Co', FilingStatus::Paid);

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('lien.view');

        $this->actingAs($viewer);

        Livewire::test(LienBoard::class)
            ->assertSee('Viewer Co')
            ->assertDontSeeHtml("start('{$filing->public_id}')")
            ->call('start', $filing->public_id)
            ->assertForbidden();

        expect($filing->fresh()->status)->toBe(FilingStatus::Paid)
            ->and($filing->events()->count())->toBe(0);
    });

    it('does nothing to a card that has already left New', function () {
        $filing = boardFiling('Moved On Co', FilingStatus::NeedsReview);

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)->call('start', $filing->public_id);

        expect($filing->fresh()->status)->toBe(FilingStatus::NeedsReview)
            ->and($filing->events()->count())->toBe(0);
    });
});

describe('ages', function () {
    it('turns a card red after more than 2 business days with no action from us', function () {
        $stale = boardFiling('Stale Co', FilingStatus::Paid, ['paid_at' => boardEastern('2026-10-19 10:00')]);
        $fresh = boardFiling('Fresh Co', FilingStatus::Paid, ['paid_at' => boardEastern('2026-10-20 10:00')]);

        expect(boardCardFor($stale)->alert)->toBe('No action for 3 business days')
            ->and(boardCardFor($fresh)->alert)->toBeNull();

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)->assertSee('No action for 3 business days');
    });

    it('does not count the weekend', function () {
        $filing = boardFiling('Friday Co', FilingStatus::Paid, ['paid_at' => boardEastern('2026-10-16 16:00')]);

        $this->travelTo(boardEastern('2026-10-20 09:00'));
        expect(boardCardFor($filing)->alert)->toBeNull();

        $this->travelTo(boardEastern('2026-10-21 09:00'));
        expect(boardCardFor($filing)->alert)->toBe('No action for 3 business days');
    });

    it('counts from our last note, request or status change', function (string $type, array $payload) {
        $filing = boardFiling('Worked Co', FilingStatus::ReadyToFile, ['paid_at' => boardEastern('2026-10-01 10:00')]);
        boardEvent($filing, $type, boardEastern('2026-10-21 11:00'), $this->admin, $payload);

        expect(boardCardFor($filing)->alert)->toBeNull();
    })->with([
        'note' => ['note_added', ['comment' => 'Called the county.']],
        'request' => ['client_request_sent', ['text' => 'Send the contract.']],
        'status change' => ['status_changed', ['from' => 'needs_review', 'to' => 'ready_to_file', 'meta' => []]],
    ]);

    it('ignores status changes we did not make', function () {
        $filing = boardFiling('Webhook Co', FilingStatus::NeedsReview, ['paid_at' => boardEastern('2026-10-01 10:00')]);
        boardEvent($filing, 'status_changed', boardEastern('2026-10-14 10:00'), $this->admin, ['from' => 'paid', 'to' => 'awaiting_esign', 'meta' => []]);
        // The signer's e-signature moved it to Needs Review.
        boardEvent($filing, 'status_changed', boardEastern('2026-10-21 10:00'), $this->customer, ['from' => 'awaiting_esign', 'to' => 'needs_review', 'meta' => []]);

        expect(boardCardFor($filing)->alert)->toBe('No action for 6 business days');
    });

    it('turns a customer answer red after a business day with nothing from us', function () {
        $filing = boardFiling('Answered Co', FilingStatus::NeedsReview, ['paid_at' => boardEastern('2026-10-01 10:00')]);
        boardEvent($filing, 'client_request_sent', boardEastern('2026-10-05 10:00'), $this->admin, ['text' => 'Send the contract.']);
        boardEvent($filing, 'client_response', boardEastern('2026-10-21 16:00'), $this->customer, ['text' => 'Attached.']);

        expect(boardCardFor($filing)->alert)->toBe("Customer's answer waiting 1 business day");

        boardEvent($filing, 'note_added', boardEastern('2026-10-22 09:00'), $this->admin, ['comment' => 'Got it, reviewing.']);

        expect(boardCardFor($filing)->alert)->toBeNull();
    });

    it('does not turn a fresh customer answer red', function () {
        $filing = boardFiling('Just Answered Co', FilingStatus::NeedsReview, ['paid_at' => boardEastern('2026-10-01 10:00')]);
        boardEvent($filing, 'client_request_sent', boardEastern('2026-10-05 10:00'), $this->admin, ['text' => 'Send the contract.']);
        boardEvent($filing, 'client_response', boardEastern('2026-10-22 11:00'), $this->customer, ['text' => 'Attached.']);

        expect(boardCardFor($filing)->alert)->toBeNull();
    });

    it('leaves cards that are not ours to move uncoloured', function (FilingStatus $status) {
        $filing = boardFiling('Parked Co', $status, ['paid_at' => boardEastern('2026-09-01 10:00')]);

        expect(boardCardFor($filing)->alert)->toBeNull();
    })->with([
        'waiting on customer' => [FilingStatus::AwaitingClient],
        'waiting on next step' => [FilingStatus::WaitingOnNextStep],
        'hold' => [FilingStatus::Hold],
        'submitted for recording' => [FilingStatus::SubmittedForRecording],
        'recorded' => [FilingStatus::Recorded],
        'mailed' => [FilingStatus::Mailed],
    ]);
});

describe('card details', function () {
    it('shows the start of the request on a Waiting on customer card, and when it was sent', function () {
        $text = 'Please send the signed subcontract and the last two invoices you sent the general contractor, so we can check amounts.';
        boardFiling('Waiting Co', FilingStatus::AwaitingClient, [
            'client_request_json' => [
                'text' => $text,
                'requested_at' => now()->subDays(2)->toIso8601String(),
                'requested_by_user_id' => $this->admin->id,
                'attachment_media_id' => null,
                'status' => 'awaiting_client',
                'resolved_at' => null,
            ],
        ]);

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)
            ->assertSee(Str::limit($text, 80))
            ->assertDontSee('check amounts')
            ->assertSee('Sent 2 days ago');
    });

    it('shows when the customer answered on a Needs Review card', function () {
        $filing = boardFiling('Reply Co', FilingStatus::NeedsReview, [
            'client_request_json' => [
                'text' => 'Send the contract.',
                'requested_at' => now()->subDays(3)->toIso8601String(),
                'requested_by_user_id' => $this->admin->id,
                'attachment_media_id' => null,
                'status' => 'awaiting_client',
                'resolved_at' => now()->subHours(3)->toIso8601String(),
            ],
        ]);
        boardEvent($filing, 'client_response', now()->subHours(3), $this->customer, ['text' => 'Attached.']);

        expect(boardCardFor($filing)->customerAnswered())->toBeTrue()
            ->and($filing->customerRepliedAt()->equalTo(now()->subHours(3)))->toBeTrue();

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)->assertSee('Customer answered 3 hours ago');
    });

    it('shows the hold reason on a Hold card', function () {
        $filing = boardFiling('Paused Co', FilingStatus::Hold);
        boardEvent($filing, 'status_changed', now()->subDay(), $this->admin, [
            'from' => 'needs_review',
            'to' => 'hold',
            'meta' => ['note' => 'Waiting on the county to reopen.'],
        ]);

        $this->actingAs($this->admin);

        Livewire::test(LienBoard::class)->assertSee('Hold: Waiting on the county to reopen.');
    });
});

describe('ordering', function () {
    it('sorts each column by deadline (none last), then by payment date', function () {
        $deadline = function (LienFiling $filing, string $date): void {
            $due = LienProjectDeadline::factory()->forProject($filing->project)->create(['due_date' => $date]);
            $filing->update(['project_deadline_id' => $due->id]);
        };

        $noDeadlineOld = boardFiling('No Deadline Old', FilingStatus::Paid, ['paid_at' => now()->subDays(9)]);
        $late = boardFiling('Late Deadline', FilingStatus::Paid, ['paid_at' => now()->subDays(8)]);
        $soonNew = boardFiling('Soon Deadline New', FilingStatus::Paid, ['paid_at' => now()->subDay()]);
        $soonOld = boardFiling('Soon Deadline Old', FilingStatus::Paid, ['paid_at' => now()->subDays(2)]);
        $noDeadlineNew = boardFiling('No Deadline New', FilingStatus::Paid, ['paid_at' => now()->subHour()]);

        $deadline($late, '2026-12-01');
        $deadline($soonNew, '2026-11-02');
        $deadline($soonOld, '2026-11-02');

        $this->actingAs($this->admin);

        $component = Livewire::test(LienBoard::class);

        expect($component->instance()->getFilings()->get('new')->pluck('id')->all())->toBe([
            $soonOld->id,
            $soonNew->id,
            $late->id,
            $noDeadlineOld->id,
            $noDeadlineNew->id,
        ]);

        $component->assertSeeInOrder(['Soon Deadline Old', 'Soon Deadline New', 'Late Deadline', 'No Deadline Old', 'No Deadline New']);
    });
});
