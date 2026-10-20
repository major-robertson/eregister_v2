<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Notifications\DeadlineApproaching;
use App\Mail\AbandonedCheckoutReminder;
use App\Mail\FilingActionReminder;
use App\Mail\PaymentReceipt;
use App\Mail\ReviewRequest;
use App\Mail\SignerInvitation;
use App\Mail\SignerReminder;
use App\Mail\WorkingOnOrder;
use App\Models\EmailSequence;
use App\Models\Payment;
use App\Models\User;
use App\Services\StripeRefundRecorder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
| EREG-131: automated lien mail that went to the wrong people. A filing that
| ends stops its own emails; "finish your order" never goes to a business
| with a paid lien order being worked on; replies to customer mail reach the
| support inbox; and the filing page and the "we need information" reminder
| tell the customer the same thing.
*/

beforeEach(function () {
    Storage::fake('s3');

    $this->user = User::factory()->create(['first_name' => 'Dana']);
    $this->business = Business::factory()->onboarded()->withLienOnboarding()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'created_by_user_id' => $this->user->id,
    ]);

    $this->prelim = LienDocumentType::where('slug', 'prelim_notice')->firstOrFail();
    $this->lien = LienDocumentType::where('slug', 'mechanics_lien')->firstOrFail();
});

if (! function_exists('hygieneSequence')) {
    /** A running sequence of $type on $sequenceable, due now. */
    function hygieneSequence(string $type, $sequenceable, User $user, Business $business, array $overrides = []): EmailSequence
    {
        return EmailSequence::create(array_merge([
            'sequence_type' => $type,
            'sequenceable_type' => $sequenceable->getMorphClass(),
            'sequenceable_id' => $sequenceable->getKey(),
            'user_id' => $user->id,
            'business_id' => $business->id,
            'customer_type' => 'new',
            'next_send_at' => now()->subMinute(),
        ], $overrides));
    }
}

if (! function_exists('hygieneSignatureRequest')) {
    /** An e-sign session awaiting the filing creator's signature, made directly (no PDFs). */
    function hygieneSignatureRequest(LienFiling $filing, User $signer): SignatureRequest
    {
        return SignatureRequest::create([
            'signable_type' => $filing->getMorphClass(),
            'signable_id' => $filing->id,
            'business_id' => $filing->business_id,
            'signer_user_id' => $signer->id,
            'document_signing_policy_key' => 'demand_letter',
            'status' => SignatureRequestStatus::AwaitingSignature,
            'signer_name_snapshot' => $signer->name,
            'signer_email_snapshot' => $signer->email,
            'invited_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);
    }
}

describe('a filing that ends stops its own emails', function () {
    beforeEach(function () {
        $this->filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->prelim->id,
            'status' => FilingStatus::ReadyToFile,
            'paid_at' => now()->subWeek(),
            'created_by_user_id' => $this->user->id,
        ]);

        // One of each kind a filing carries: an old "finish your order"
        // series and a "we need information" reminder.
        $this->sequences = collect([
            hygieneSequence('abandon_checkout', $this->filing, $this->user, $this->business),
            hygieneSequence('filing_action_reminder', $this->filing, $this->user, $this->business, [
                'trigger_status' => FilingStatus::AwaitingClient->value,
            ]),
        ]);
    });

    it('suppresses every sequence on the filing when it is canceled or completed', function (FilingStatus $status, string $reason) {
        $this->filing->transitionTo($status);

        foreach ($this->sequences as $sequence) {
            $sequence->refresh();

            expect($sequence->suppressed_at)->not->toBeNull()
                ->and($sequence->suppression_reason)->toBe($reason)
                ->and($sequence->next_send_at)->toBeNull();
        }
    })->with([
        'canceled' => [FilingStatus::Canceled, 'filing_canceled'],
        'complete' => [FilingStatus::Complete, 'filing_complete'],
    ]);

    it('suppresses every sequence on the filing when it is refunded', function () {
        // Refunds set the status directly, never through transitionTo().
        $payment = Payment::factory()->forPurchasable($this->filing)->succeeded()->create(['amount_cents' => 4900]);

        app(StripeRefundRecorder::class)->record($payment, 4900, 're_hygiene');

        expect($this->filing->fresh()->status)->toBe(FilingStatus::Refunded);

        foreach ($this->sequences as $sequence) {
            expect($sequence->refresh()->suppression_reason)->toBe('filing_refunded');
        }
    });

    it('sends nothing more once the filing has ended', function () {
        Mail::fake();

        $this->filing->transitionTo(FilingStatus::Canceled);
        $this->artisan('email:process-sequences')->assertSuccessful();

        Mail::assertNothingQueued();
    });

    it('leaves sequences on other filings alone', function () {
        $other = LienFiling::factory()->forProject($this->project)->draft()->create([
            'document_type_id' => $this->lien->id,
            'created_by_user_id' => $this->user->id,
        ]);
        $otherSequence = hygieneSequence('abandon_checkout', $other, $this->user, $this->business);

        $this->filing->transitionTo(FilingStatus::Complete);

        expect($otherSequence->refresh()->suppressed_at)->toBeNull();
    });

    it('does nothing on a status change that does not end the filing', function () {
        $this->filing->transitionTo(FilingStatus::InFulfillment);

        foreach ($this->sequences as $sequence) {
            expect($sequence->refresh()->suppressed_at)->toBeNull();
        }
    });
});

describe('"finish your order" never goes to a paying lien customer', function () {
    beforeEach(function () {
        $this->draft = LienFiling::factory()->forProject($this->project)->draft()->create([
            'document_type_id' => $this->lien->id,
            'created_by_user_id' => $this->user->id,
        ]);
    });

    it('starts for a business with no paid filing', function () {
        $sequence = EmailSequence::startFor('abandon_checkout', $this->draft, $this->user, $this->business, 'https://example.test/resume');

        expect($sequence)->not->toBeNull()
            ->and($sequence->shouldSuppress())->toBeNull();
    });

    it('does not start, and stops one already running, while another paid order is being worked on', function (FilingStatus $status) {
        $running = hygieneSequence('abandon_checkout', $this->draft, $this->user, $this->business);

        // A different job, so this is not a duplicate of it.
        $otherProject = LienProject::factory()->forBusiness($this->business)->create();
        LienFiling::factory()->forProject($otherProject)->create([
            'document_type_id' => $this->prelim->id,
            'status' => $status,
            'paid_at' => now()->subDays(10),
        ]);

        $newDraft = LienFiling::factory()->forProject($this->project)->draft()->create([
            'document_type_id' => $this->prelim->id,
        ]);

        expect(EmailSequence::startFor('abandon_checkout', $newDraft, $this->user, $this->business))->toBeNull()
            ->and(EmailSequence::whereMorphedTo('sequenceable', $newDraft)->exists())->toBeFalse()
            ->and($running->shouldSuppress())->toBe('paid_filing_in_progress');
    })->with([
        'paid' => FilingStatus::Paid,
        'awaiting client' => FilingStatus::AwaitingClient,
        'awaiting e-sign' => FilingStatus::AwaitingEsign,
        'awaiting notary' => FilingStatus::AwaitingNotary,
        'needs review' => FilingStatus::NeedsReview,
        'ready to file' => FilingStatus::ReadyToFile,
        'waiting on next step' => FilingStatus::WaitingOnNextStep,
        'on hold' => FilingStatus::Hold,
        'in fulfillment' => FilingStatus::InFulfillment,
        'submitted for recording' => FilingStatus::SubmittedForRecording,
        'recorded' => FilingStatus::Recorded,
        'mailed' => FilingStatus::Mailed,
    ]);

    it('suppresses at send time without sending', function () {
        Mail::fake();

        $running = hygieneSequence('abandon_checkout', $this->draft, $this->user, $this->business);
        $otherProject = LienProject::factory()->forBusiness($this->business)->create();
        LienFiling::factory()->forProject($otherProject)->create([
            'document_type_id' => $this->prelim->id,
            'status' => FilingStatus::InFulfillment,
            'paid_at' => now()->subDay(),
        ]);

        $this->artisan('email:process-sequences')->assertSuccessful();

        Mail::assertNotQueued(AbandonedCheckoutReminder::class);
        expect($running->refresh()->suppression_reason)->toBe('paid_filing_in_progress');
    });

    it('still starts when the other paid order is older than 90 days or has ended', function (FilingStatus $status, int $paidDaysAgo) {
        $otherProject = LienProject::factory()->forBusiness($this->business)->create();
        LienFiling::factory()->forProject($otherProject)->create([
            'document_type_id' => $this->prelim->id,
            'status' => $status,
            'paid_at' => now()->subDays($paidDaysAgo),
        ]);

        expect(EmailSequence::startFor('abandon_checkout', $this->draft, $this->user, $this->business))->not->toBeNull();
    })->with([
        'in fulfillment, paid 91 days ago' => [FilingStatus::InFulfillment, 91],
        'complete' => [FilingStatus::Complete, 5],
        'canceled' => [FilingStatus::Canceled, 5],
        'refunded' => [FilingStatus::Refunded, 5],
    ]);

    it('ignores a paid filing at another business', function () {
        $stranger = Business::factory()->create();
        $strangerProject = LienProject::factory()->forBusiness($stranger)->create();
        LienFiling::factory()->forProject($strangerProject)->create([
            'document_type_id' => $this->prelim->id,
            'status' => FilingStatus::InFulfillment,
            'paid_at' => now()->subDay(),
        ]);

        expect(EmailSequence::startFor('abandon_checkout', $this->draft, $this->user, $this->business))->not->toBeNull();
    });

    it('does not start, and stops one already running, for a draft that repeats a filing they paid for on the same job', function () {
        $running = hygieneSequence('abandon_checkout', $this->draft, $this->user, $this->business);

        // Same job, same document, paid long ago and long finished.
        LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->lien->id,
            'status' => FilingStatus::Complete,
            'paid_at' => now()->subYear(),
        ]);

        $secondDraft = LienFiling::factory()->forProject($this->project)->draft()->create([
            'document_type_id' => $this->lien->id,
        ]);

        expect(EmailSequence::startFor('abandon_checkout', $secondDraft, $this->user, $this->business))->toBeNull()
            ->and($running->shouldSuppress())->toBe('duplicate_of_paid_filing');
    });

    it('still starts when the earlier order on the same job was refunded or canceled', function (FilingStatus $status) {
        // The wizard lets these customers start over.
        LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->lien->id,
            'status' => $status,
            'paid_at' => now()->subMonth(),
        ]);

        expect(EmailSequence::startFor('abandon_checkout', $this->draft, $this->user, $this->business))->not->toBeNull();
    })->with([
        'refunded' => FilingStatus::Refunded,
        'canceled' => FilingStatus::Canceled,
    ]);

    it('still starts for a different document on the same job', function () {
        LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->prelim->id,
            'status' => FilingStatus::Complete,
            'paid_at' => now()->subYear(),
        ]);

        expect(EmailSequence::startFor('abandon_checkout', $this->draft, $this->user, $this->business))->not->toBeNull();
    });

    it('covers the onboarding sequence on the first project too', function () {
        expect(EmailSequence::startFor('abandon_checkout', $this->project, $this->user, $this->business))->not->toBeNull();

        LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->prelim->id,
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $running = EmailSequence::whereMorphedTo('sequenceable', $this->project)->sole();
        expect($running->shouldSuppress())->toBe('paid_filing_in_progress');

        $secondProject = LienProject::factory()->forBusiness($this->business)->create();
        expect(EmailSequence::startFor('abandon_checkout', $secondProject, $this->user, $this->business))->toBeNull();
    });

    it('leaves other products alone', function () {
        LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->prelim->id,
            'status' => FilingStatus::InFulfillment,
            'paid_at' => now(),
        ]);

        $application = FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['TX'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
        ]);

        $sequence = EmailSequence::startFor('abandon_checkout', $application, $this->user, $this->business);

        expect($sequence)->not->toBeNull()
            ->and($sequence->shouldSuppress())->toBeNull();
    });
});

describe('replies reach the support inbox', function () {
    beforeEach(function () {
        $this->filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->prelim->id,
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
            'created_by_user_id' => $this->user->id,
        ]);
    });

    it('sets Reply-To on every customer mailable in the filing flow', function () {
        $this->filing->transitionTo(FilingStatus::AwaitingClient);
        $reminder = EmailSequence::whereMorphedTo('sequenceable', $this->filing)->where('sequence_type', 'filing_action_reminder')->sole();

        $draft = LienFiling::factory()->forProject($this->project)->draft()->create(['document_type_id' => $this->lien->id]);
        $abandon = hygieneSequence('abandon_checkout', $draft, $this->user, $this->business);

        $payment = Payment::factory()->forPurchasable($this->filing)->succeeded()->create();
        $signatureRequest = hygieneSignatureRequest($this->filing, $this->user);

        $mailables = [
            new FilingActionReminder($reminder, 1),
            new AbandonedCheckoutReminder($abandon, 1),
            new PaymentReceipt($payment),
            new WorkingOnOrder($payment),
            new ReviewRequest($this->user),
            new SignerInvitation($signatureRequest),
            new SignerReminder($signatureRequest),
        ];

        foreach ($mailables as $mailable) {
            expect($mailable->hasReplyTo('contact@eregister.com'))->toBeTrue(class_basename($mailable).' has no support Reply-To');
        }
    });

    it('sets Reply-To on the deadline reminder', function () {
        $deadline = LienProjectDeadline::factory()->forProject($this->project)->create([
            'document_type_id' => $this->prelim->id,
            'due_date' => now()->addWeek()->toDateString(),
        ]);

        $mail = (new DeadlineApproaching($deadline, 7))->toMail($this->user);

        expect($mail->replyTo)->toBe([['contact@eregister.com', null]]);
    });

    it('reads the address from config', function () {
        config(['mail.support_address' => 'help@example.test']);

        $payment = Payment::factory()->forPurchasable($this->filing)->succeeded()->create();

        expect((new PaymentReceipt($payment))->hasReplyTo('help@example.test'))->toBeTrue()
            ->and((new PaymentReceipt($payment))->hasReplyTo('contact@eregister.com'))->toBeFalse();
    });
});

describe('the "we need information" copy', function () {
    it('tells the customer the same thing on the filing page and in the reminder', function () {
        $sentence = 'Please check the request we emailed you and reply to it.';

        expect(FilingStatus::AwaitingClient->whatsNext())->toBe($sentence);

        $context = FilingStatus::AwaitingClient->reminderContext();
        expect($context['body'])->toContain($sentence)
            ->and($context['cta_label'])->not->toBe('Provide Information');
    });

    it('shows it on the filing page', function () {
        $filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $this->prelim->id,
            'status' => FilingStatus::AwaitingClient,
            'paid_at' => now(),
            'created_by_user_id' => $this->user->id,
        ]);

        $this->actingAs($this->user);
        session(['current_business_id' => $this->business->id]);

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->assertSee('Please check the request we emailed you and reply to it.')
            ->assertDontSee('respond to our email');
    });
});
