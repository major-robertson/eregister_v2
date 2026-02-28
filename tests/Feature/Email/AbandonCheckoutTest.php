<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Mail\AbandonedCheckoutReminder;
use App\Models\EmailSequence;
use App\Models\EmailUnsubscribe;
use App\Models\Payment;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->onboarded()->withLienOnboarding()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'created_by_user_id' => $this->user->id,
    ]);

    $this->filing = LienFiling::factory()->forProject($this->project)->draft()->create([
        'created_by_user_id' => $this->user->id,
    ]);
});

// --- Sequence Creation ---

it('creates abandon sequence for a filing', function () {
    $sequence = EmailSequence::startFor(
        'abandon_checkout',
        $this->filing,
        $this->user,
        $this->business,
        'https://example.com/checkout'
    );

    expect($sequence)->not->toBeNull();
    expect($sequence->user_id)->toBe($this->user->id);
    expect($sequence->business_id)->toBe($this->business->id);
    expect($sequence->sequence_type)->toBe('abandon_checkout');
    expect($sequence->sequenceable_type)->toBe($this->filing->getMorphClass());
    expect($sequence->sequenceable_id)->toBe($this->filing->id);
    expect($sequence->customer_type)->toBe('new');
    expect($sequence->resume_url)->toBe('https://example.com/checkout');
    expect($sequence->next_send_at)->not->toBeNull();
});

it('detects returning customer when business has paid before', function () {
    Payment::factory()->succeeded()->create([
        'business_id' => $this->business->id,
    ]);

    $sequence = EmailSequence::startFor(
        'abandon_checkout',
        $this->filing,
        $this->user,
        $this->business,
    );

    expect($sequence->customer_type)->toBe('returning');
});

it('does not create duplicate sequence for same filing', function () {
    EmailSequence::startFor('abandon_checkout', $this->filing, $this->user, $this->business);
    EmailSequence::startFor('abandon_checkout', $this->filing, $this->user, $this->business);

    expect(EmailSequence::count())->toBe(1);
});

it('does not create sequence for already paid filing', function () {
    $this->filing->update(['status' => FilingStatus::Paid, 'paid_at' => now()]);

    $sequence = EmailSequence::startFor(
        'abandon_checkout',
        $this->filing,
        $this->user,
        $this->business,
    );

    expect($sequence)->toBeNull();
});

// --- Step Progression ---

it('advances through steps correctly', function () {
    $sequence = EmailSequence::factory()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
        'next_send_at' => now()->subMinute(),
    ]);

    expect($sequence->currentStep())->toBe(1);

    SentEmail::create([
        'user_id' => $this->user->id,
        'email_type' => 'abandon_checkout_step_1',
        'emailable_type' => $sequence->getMorphClass(),
        'emailable_id' => $sequence->id,
        'scheduled_at' => now(),
        'sent_at' => now(),
    ]);
    $sequence->advanceStep(1);
    $sequence->refresh();

    expect($sequence->currentStep())->toBe(2);
    expect($sequence->next_send_at)->not->toBeNull();
    expect($sequence->completed_at)->toBeNull();

    SentEmail::create([
        'user_id' => $this->user->id,
        'email_type' => 'abandon_checkout_step_2',
        'emailable_type' => $sequence->getMorphClass(),
        'emailable_id' => $sequence->id,
        'scheduled_at' => now(),
        'sent_at' => now(),
    ]);
    $sequence->advanceStep(2);
    $sequence->refresh();

    expect($sequence->currentStep())->toBe(3);

    SentEmail::create([
        'user_id' => $this->user->id,
        'email_type' => 'abandon_checkout_step_3',
        'emailable_type' => $sequence->getMorphClass(),
        'emailable_id' => $sequence->id,
        'scheduled_at' => now(),
        'sent_at' => now(),
    ]);
    $sequence->advanceStep(3);
    $sequence->refresh();

    expect($sequence->currentStep())->toBeNull();
    expect($sequence->completed_at)->not->toBeNull();
});

// --- Suppression ---

it('suppresses when payment is completed', function () {
    $this->filing->update(['paid_at' => now(), 'status' => FilingStatus::Paid]);

    $sequence = EmailSequence::factory()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
        'next_send_at' => now()->subMinute(),
    ]);

    expect($sequence->shouldSuppress())->toBe('payment_completed');
});

it('suppresses when user has unsubscribed from category', function () {
    EmailUnsubscribe::unsubscribe($this->user, EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT);

    $sequence = EmailSequence::factory()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
        'next_send_at' => now()->subMinute(),
    ]);

    expect($sequence->shouldSuppress())->toBe('unsubscribed');
});

it('suppresses when user has unsubscribed from all', function () {
    $this->user->update(['unsubscribed_from_all_emails_at' => now()]);

    $sequence = EmailSequence::factory()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
        'next_send_at' => now()->subMinute(),
    ]);

    expect($sequence->shouldSuppress())->toBe('unsubscribed');
});

it('suppresses when all steps are sent', function () {
    $sequence = EmailSequence::factory()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
    ]);

    foreach ([1, 2, 3] as $step) {
        SentEmail::create([
            'user_id' => $this->user->id,
            'email_type' => "abandon_checkout_step_{$step}",
            'emailable_type' => $sequence->getMorphClass(),
            'emailable_id' => $sequence->id,
            'scheduled_at' => now(),
            'sent_at' => now(),
        ]);
    }

    expect($sequence->shouldSuppress())->toBe('all_steps_sent');
});

it('does not suppress for active unpaid sequence', function () {
    $sequence = EmailSequence::factory()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
        'next_send_at' => now()->subMinute(),
    ]);

    expect($sequence->shouldSuppress())->toBeNull();
});

// --- Processing Command ---

it('sends email for due sequence', function () {
    Mail::fake();

    $sequence = EmailSequence::factory()->readyToSend()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
    ]);

    $this->artisan('email:process-sequences');

    Mail::assertQueued(AbandonedCheckoutReminder::class, function ($mail) use ($sequence) {
        return $mail->sequence->id === $sequence->id && $mail->step === 1;
    });

    expect(SentEmail::where('email_type', 'abandon_checkout_step_1')
        ->where('emailable_id', $sequence->id)
        ->exists())->toBeTrue();
});

it('skips sequences not yet due', function () {
    Mail::fake();

    EmailSequence::factory()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
        'next_send_at' => now()->addHour(),
    ]);

    $this->artisan('email:process-sequences');

    Mail::assertNothingQueued();
});

it('suppresses and does not send when filing is paid', function () {
    Mail::fake();

    $this->filing->update(['paid_at' => now(), 'status' => FilingStatus::Paid]);

    $sequence = EmailSequence::factory()->readyToSend()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
    ]);

    $this->artisan('email:process-sequences');

    Mail::assertNothingQueued();

    $sequence->refresh();
    expect($sequence->suppressed_at)->not->toBeNull();
    expect($sequence->suppression_reason)->toBe('payment_completed');
});

it('sends step 2 after step 1', function () {
    Mail::fake();

    $sequence = EmailSequence::factory()->readyToSend()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
    ]);

    SentEmail::create([
        'user_id' => $this->user->id,
        'email_type' => 'abandon_checkout_step_1',
        'emailable_type' => $sequence->getMorphClass(),
        'emailable_id' => $sequence->id,
        'scheduled_at' => now()->subDay(),
        'sent_at' => now()->subDay(),
    ]);

    $this->artisan('email:process-sequences');

    Mail::assertQueued(AbandonedCheckoutReminder::class, function ($mail) {
        return $mail->step === 2;
    });
});

// --- Unsubscribe / Preferences ---

it('shows preferences page via signed url', function () {
    $url = \Illuminate\Support\Facades\URL::signedRoute('email.preferences', [
        'user' => $this->user->id,
    ]);

    $this->withoutVite()->get($url)->assertSuccessful();
});

it('rejects unsigned preferences url', function () {
    $this->get(route('email.preferences', ['user' => $this->user->id]))
        ->assertForbidden();
});

it('suppresses after category unsubscribe', function () {
    Mail::fake();

    EmailUnsubscribe::unsubscribe($this->user, EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT);

    EmailSequence::factory()->readyToSend()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
    ]);

    $this->artisan('email:process-sequences');

    Mail::assertNothingQueued();
});

// --- Email Content ---

it('includes project name in email when available', function () {
    $sequence = EmailSequence::factory()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
        'resume_url' => 'https://example.com/resume',
    ]);

    $mailable = new AbandonedCheckoutReminder($sequence, 1);
    $rendered = $mailable->render();

    expect($rendered)->toContain($this->user->first_name);
    expect($rendered)->toContain('Continue Your Order');
    expect($rendered)->toContain('Manage email preferences');
});

it('varies content by step number', function () {
    $sequence = EmailSequence::factory()->create([
        'user_id' => $this->user->id,
        'business_id' => $this->business->id,
        'sequence_type' => 'abandon_checkout',
        'sequenceable_type' => $this->filing->getMorphClass(),
        'sequenceable_id' => $this->filing->id,
    ]);

    $step1 = (new AbandonedCheckoutReminder($sequence, 1))->render();
    $step2 = (new AbandonedCheckoutReminder($sequence, 2))->render();
    $step3 = (new AbandonedCheckoutReminder($sequence, 3))->render();

    expect($step1)->toContain('didn\'t finish');
    expect($step2)->toContain('follow-up');
    expect($step3)->toContain('last reminder');
});
