<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Admin\Livewire\SalesTaxApplicationStateDetail;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Mail\FilingActionRequested;
use App\Mail\UploadReceived;
use App\Models\EmailLog;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

/*
| EREG-154: every email the app sends gets an email_logs row, linked to the
| record it is about; Postmark's webhook adds delivery and bounces; the admin
| lien filing and sales tax pages show the emails in their Activity history.
*/

beforeEach(function () {
    Storage::fake('s3');

    $this->customer = User::factory()->create([
        'first_name' => 'Dana',
        'email' => 'dana@example.test',
    ]);
    $this->business = Business::factory()->onboarded()->withLienOnboarding()->create();
    $this->business->users()->attach($this->customer, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Maple Street Remodel',
        'created_by_user_id' => $this->customer->id,
    ]);

    $this->filing = LienFiling::factory()->forProject($this->project)->paid()->create([
        'document_type_id' => LienDocumentType::where('slug', 'prelim_notice')->firstOrFail()->id,
        'created_by_user_id' => $this->customer->id,
    ]);

    $this->admin = User::factory()->create(['first_name' => 'Major']);
    $this->admin->givePermissionTo(['lien.view', 'lien.update', 'lien.change_status']);
});

describe('logging sends', function () {
    it('logs a mailable with its record, recipient, subject and body', function () {
        Mail::to($this->customer)->send(new UploadReceived($this->filing, 'contract.pdf', $this->customer));

        $log = EmailLog::sole();

        expect($log->mailable)->toBe(UploadReceived::class)
            ->and($log->related_type)->toBe('lien_filing')
            ->and($log->related_id)->toBe($this->filing->id)
            ->and($log->to)->toBe(['dana@example.test'])
            ->and($log->user_id)->toBe($this->customer->id)
            ->and($log->subject)->not->toBeEmpty()
            ->and($log->html_body)->toContain('contract.pdf')
            ->and($log->message_id)->not->toBeEmpty()
            ->and($log->status)->toBe(EmailLog::STATUS_SENT);
    });

    it('logs the request email an admin sends with Awaiting Client', function () {
        $this->actingAs($this->admin);

        app(ChangeFilingStatus::class)->execute(
            filing: $this->filing,
            newStatus: FilingStatus::AwaitingClient,
            customerMessage: 'Please send the signed contract.',
        );

        $log = EmailLog::where('mailable', FilingActionRequested::class)->sole();

        expect($log->related_id)->toBe($this->filing->id)
            ->and($log->html_body)->toContain('Please send the signed contract.');
    });

    it('logs Mail::raw() with its text body', function () {
        Mail::raw('Plain hello', fn ($message) => $message->to('someone@example.test')->subject('Hello'));

        $raw = EmailLog::sole();
        expect($raw->mailable)->toBeNull()
            ->and($raw->text_body)->toBe('Plain hello')
            ->and($raw->html_body)->toBeNull()
            ->and($raw->user_id)->toBeNull();
    });

    it('logs a password reset without its body', function () {
        $this->customer->sendPasswordResetNotification('secret-token');

        $log = EmailLog::sole();

        expect($log->mailable)->toBe(\Illuminate\Auth\Notifications\ResetPassword::class)
            ->and($log->body_withheld)->toBeTrue()
            ->and($log->html_body)->toBeNull()
            ->and($log->text_body)->toBeNull();
    });

    it('breaks the signature on signed links before storing', function () {
        $url = URL::temporarySignedRoute('esign.sign', now()->addDay(), ['request' => 'abc123']);
        $signature = Illuminate\Support\Str::after($url, 'signature=');

        Mail::html('<a href="'.$url.'">Sign now</a>', fn ($message) => $message->to('someone@example.test')->subject('Sign'));

        $log = EmailLog::sole();

        expect($log->html_body)->toContain('signature=removed')
            ->and($log->html_body)->not->toContain($signature);
    });

    it('never fails the send when logging fails', function () {
        EmailLog::creating(fn () => throw new RuntimeException('database down'));

        Mail::raw('Still goes out', fn ($message) => $message->to('someone@example.test')->subject('Hello'));

        expect(EmailLog::count())->toBe(0);
    });

    it('resolves a sequence to the record it serves', function () {
        $sequence = EmailSequence::startReminderFor($this->filing, FilingStatus::AwaitingClient, $this->customer, $this->business);

        expect(EmailLog::about($sequence))->toBe([
            EmailLog::META_TYPE => 'lien_filing',
            EmailLog::META_ID => (string) $this->filing->id,
        ])->and(EmailLog::about(null))->toBe([]);
    });
});

describe('postmark events', function () {
    beforeEach(function () {
        config()->set('services.postmark.webhook_token', 'test-token');

        $this->log = EmailLog::factory()->about($this->filing)->create([
            'to' => ['dana@example.test'],
            'bcc' => ['contact@eregister.com'],
            'message_id' => 'pm-123',
        ]);
    });

    function emailLogWebhook(array $payload): void
    {
        test()->postJson(route('webhooks.postmark', ['token' => 'test-token']), $payload)->assertSuccessful();
    }

    it('marks the email delivered', function () {
        emailLogWebhook(['RecordType' => 'Delivery', 'MessageID' => 'pm-123', 'Recipient' => 'Dana@Example.test', 'DeliveredAt' => '2026-10-21T15:00:00Z']);

        expect($this->log->refresh()->status)->toBe(EmailLog::STATUS_DELIVERED)
            ->and($this->log->delivered_at->toIso8601String())->toBe('2026-10-21T15:00:00+00:00');
    });

    it('marks the email bounced with the bounce name', function () {
        emailLogWebhook(['RecordType' => 'Bounce', 'Type' => 'HardBounce', 'Name' => 'Hard bounce', 'MessageID' => 'pm-123', 'Email' => 'dana@example.test', 'BouncedAt' => '2026-10-21T15:00:00Z']);

        expect($this->log->refresh()->status)->toBe(EmailLog::STATUS_BOUNCED)
            ->and($this->log->status_detail)->toBe('Hard bounce');
    });

    it('ignores a delivery to a Bcc copy', function () {
        emailLogWebhook(['RecordType' => 'Delivery', 'MessageID' => 'pm-123', 'Recipient' => 'contact@eregister.com', 'DeliveredAt' => '2026-10-21T15:00:00Z']);

        expect($this->log->refresh()->status)->toBe(EmailLog::STATUS_SENT);
    });

    it('keeps the newest event when an older one arrives late', function () {
        emailLogWebhook(['RecordType' => 'Bounce', 'Name' => 'Hard bounce', 'MessageID' => 'pm-123', 'Email' => 'dana@example.test', 'BouncedAt' => '2026-10-21T16:00:00Z']);
        emailLogWebhook(['RecordType' => 'Delivery', 'MessageID' => 'pm-123', 'Recipient' => 'dana@example.test', 'DeliveredAt' => '2026-10-21T15:00:00Z']);

        expect($this->log->refresh()->status)->toBe(EmailLog::STATUS_BOUNCED);
    });

    it('keeps a spam complaint', function () {
        emailLogWebhook(['RecordType' => 'SpamComplaint', 'MessageID' => 'pm-123', 'Email' => 'dana@example.test', 'BouncedAt' => '2026-10-21T15:00:00Z']);
        emailLogWebhook(['RecordType' => 'Delivery', 'MessageID' => 'pm-123', 'Recipient' => 'dana@example.test', 'DeliveredAt' => '2026-10-21T16:00:00Z']);

        expect($this->log->refresh()->status)->toBe(EmailLog::STATUS_COMPLAINED);
    });

    it('does nothing for an unknown message', function () {
        emailLogWebhook(['RecordType' => 'Delivery', 'MessageID' => 'other', 'Recipient' => 'dana@example.test']);

        expect($this->log->refresh()->status)->toBe(EmailLog::STATUS_SENT);
    });
});

describe('admin lien filing page', function () {
    it('lists the filing\'s emails in the Activity Log and opens one', function () {
        $log = EmailLog::factory()->about($this->filing)->create([
            'subject' => 'Your lien was recorded — eRegister',
            'to' => ['dana@example.test'],
            'status' => EmailLog::STATUS_DELIVERED,
            'html_body' => '<html><head></head><body><p>Recorded copy attached.</p></body></html>',
        ]);

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->assertSee('Email: Your lien was recorded — eRegister')
            ->assertSee('to dana@example.test')
            ->assertSee('Delivered')
            ->call('viewEmail', $log->id)
            ->assertSet('showEmailModal', true)
            ->assertSee('Recorded copy attached.', escape: false);
    });

    it('shows the request email on the request entry, not as its own row', function () {
        $this->actingAs($this->admin);

        app(ChangeFilingStatus::class)->execute(
            filing: $this->filing,
            newStatus: FilingStatus::AwaitingClient,
            customerMessage: 'Please send the signed contract.',
        );

        $activity = Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->instance()
            ->getActivityLog();

        $request = $activity->firstWhere('event_type', 'client_request_sent');

        expect($request->relationLoaded('sentEmail'))->toBeTrue()
            ->and($request->getRelation('sentEmail')->mailable)->toBe(FilingActionRequested::class)
            ->and($activity->filter(fn ($entry) => $entry instanceof EmailLog)->pluck('mailable'))
            ->not->toContain(FilingActionRequested::class);
    });

    it('shows deadline reminders sent about the filing\'s project', function () {
        EmailLog::factory()->about($this->project)->create(['subject' => 'Preliminary Notice due in 7 days: Maple Street Remodel']);

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->assertSee('Preliminary Notice due in 7 days');
    });

    it('does not open an email about another filing', function () {
        $other = EmailLog::factory()->about($this->business)->create();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->call('viewEmail', $other->id)
            ->assertSet('showEmailModal', false)
            ->assertSet('viewingEmailId', null);
    });
});

describe('admin sales tax page', function () {
    it('lists the application\'s emails in Activity and opens one', function () {
        $application = FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => ['legal_name' => 'Dana Drywall LLC'],
            'created_by_user_id' => $this->customer->id,
            'paid_at' => now(),
            'submitted_at' => now(),
        ]);
        $state = FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => 'CA',
            'status' => 'complete',
            'data' => [],
            'completed_at' => now(),
        ]);

        $log = EmailLog::factory()->about($application)->create([
            'subject' => 'Your receipt from eRegister',
            'html_body' => '<p>Thanks for your payment.</p>',
        ]);

        $admin = User::factory()->create();
        $admin->givePermissionTo('tax.view');
        $this->actingAs($admin);

        Livewire::test(SalesTaxApplicationStateDetail::class, ['formApplicationState' => $state])
            ->assertSee('Email: Your receipt from eRegister')
            ->call('viewEmail', $log->id)
            ->assertSet('showEmailModal', true)
            ->assertSee('Thanks for your payment.', escape: false);
    });
});
