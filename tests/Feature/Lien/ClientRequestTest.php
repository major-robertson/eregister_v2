<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Mail\ClientResponded;
use App\Mail\FilingActionReminder;
use App\Mail\FilingActionRequested;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
| EREG-128: when an admin moves a filing to Awaiting Client / E-Signature /
| Notary, the customer gets the admin's actual request by email at once, the
| reminders quote it, and the customer answers it on the filing page.
*/

beforeEach(function () {
    Storage::fake('s3');
    Mail::fake();

    $this->customer = User::factory()->create([
        'first_name' => 'Dana',
        'last_name' => 'Builder',
        'email' => 'dana@example.test',
    ]);
    $this->business = Business::factory()->onboarded()->withLienOnboarding()->create(['name' => 'Dana Drywall LLC']);
    $this->business->users()->attach($this->customer, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Maple Street Remodel',
        'created_by_user_id' => $this->customer->id,
    ]);
    $this->prelim = LienDocumentType::where('slug', 'prelim_notice')->firstOrFail();

    $this->filing = LienFiling::factory()->forProject($this->project)->paid()->create([
        'document_type_id' => $this->prelim->id,
        'created_by_user_id' => $this->customer->id,
    ]);

    $this->admin = User::factory()->create(['first_name' => 'Major', 'last_name' => 'Robertson']);
    $this->admin->givePermissionTo(['lien.view', 'lien.update', 'lien.change_status']);
});

if (! function_exists('clientRequestPdf')) {
    /** A small upload that reads as a real PDF (the 'requests' collection checks the type). */
    function clientRequestPdf(string $name = 'contract-form.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n% test file\n");
    }
}

if (! function_exists('clientRequestFixture')) {
    /** Open a request on the filing as the admin, the way the status form does. */
    function clientRequestFixture(LienFiling $filing, User $admin, FilingStatus $status = FilingStatus::AwaitingClient, ?UploadedFile $attachment = null, string $message = 'Please send the signed contract.'): LienFiling
    {
        test()->actingAs($admin);

        app(ChangeFilingStatus::class)->execute(
            filing: $filing,
            newStatus: $status,
            customerMessage: $message,
            attachment: $attachment,
        );

        return $filing->fresh();
    }
}

if (! function_exists('clientRequestSignatureRequest')) {
    /** An e-sign session awaiting the filing creator's signature, made directly (no PDFs). */
    function clientRequestSignatureRequest(LienFiling $filing, User $signer): SignatureRequest
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

describe('the admin status form', function () {
    beforeEach(function () {
        $this->actingAs($this->admin);
    });

    it('requires a message for the three waiting statuses', function (FilingStatus $status) {
        $component = Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', $status->value)
            ->call('updateStatus')
            ->assertHasErrors(['customerMessage' => 'required']);

        expect($component->errors()->first('customerMessage'))
            ->toBe('Write the message for the customer. It is emailed to them when you save.');
        expect($this->filing->fresh()->status)->toBe(FilingStatus::Paid);
        Mail::assertNotQueued(FilingActionRequested::class);
    })->with([
        'awaiting client' => [FilingStatus::AwaitingClient],
        'awaiting e-signature' => [FilingStatus::AwaitingEsign],
        'awaiting notary' => [FilingStatus::AwaitingNotary],
    ]);

    it('asks for no message for any other status', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::ReadyToFile->value)
            ->assertDontSee('Message to the customer')
            ->assertSee('Update Status')
            ->call('updateStatus')
            ->assertHasNoErrors();

        $filing = $this->filing->fresh();
        expect($filing->status)->toBe(FilingStatus::ReadyToFile)
            ->and($filing->client_request_json)->toBeNull();
        Mail::assertNotQueued(FilingActionRequested::class);
    });

    it('shows who gets the message, the subject, and an internal-only note', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::AwaitingNotary->value)
            ->assertSee('Message to the customer')
            ->assertSee('This will be emailed to Dana Builder <dana@example.test> when you save.')
            ->assertSee('Say exactly what you need, in plain words.')
            ->assertSee("Please notarize your {$this->prelim->name} — eRegister")
            ->assertSee('Internal note (the customer never sees this)')
            ->assertSee('Send to customer and update status');
    });

    it('keeps the message under 2,000 characters', function () {
        $component = Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::AwaitingClient->value)
            ->set('customerMessage', str_repeat('a', 2001))
            ->call('updateStatus')
            ->assertHasErrors(['customerMessage' => 'max']);

        expect($component->errors()->first('customerMessage'))->toBe('Keep the message under 2,000 characters.');
    });

    it('only attaches a PDF of 10 MB or less', function (UploadedFile $file, string $rule, string $message) {
        $component = Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::AwaitingClient->value)
            ->set('customerMessage', 'Please sign the attached form.')
            ->set('customerAttachment', $file)
            ->call('updateStatus')
            ->assertHasErrors(['customerAttachment' => $rule]);

        expect($component->errors()->first('customerAttachment'))->toBe($message);
        expect($this->filing->fresh()->status)->toBe(FilingStatus::Paid);
    })->with([
        'an image' => fn () => [UploadedFile::fake()->create('photo.png', 100, 'image/png'), 'mimes', 'The attachment must be a PDF.'],
        'a large PDF' => fn () => [UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf'), 'max', 'The attachment must be 10 MB or smaller.'],
    ]);

    it('stores the request on the status event and the filing', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('newStatus', FilingStatus::AwaitingClient->value)
            ->set('customerMessage', "Please send:\n1. The signed contract\n2. Your last invoice")
            ->set('customerAttachment', clientRequestPdf())
            ->set('note', 'Called them first, no answer.')
            ->call('updateStatus')
            ->assertHasNoErrors();

        $filing = $this->filing->fresh();
        $media = $filing->getFirstMedia('requests');

        expect($filing->status)->toBe(FilingStatus::AwaitingClient)
            ->and($media)->not->toBeNull()
            ->and($media->file_name)->toBe('contract-form.pdf')
            ->and($media->disk)->toBe('s3');
        Storage::disk('s3')->assertExists($media->getPathRelativeToRoot());
        expect(Mail::queued(FilingActionRequested::class)->sole()->hasAttachment($media))->toBeTrue();

        $request = $filing->client_request_json;
        expect($request['text'])->toBe("Please send:\n1. The signed contract\n2. Your last invoice")
            ->and($request['requested_at'])->not->toBeNull()
            ->and($request['requested_by_user_id'])->toBe($this->admin->id)
            ->and($request['attachment_media_id'])->toBe($media->id)
            ->and($request['status'])->toBe('awaiting_client')
            ->and($request['resolved_at'])->toBeNull()
            ->and($filing->openClientRequest())->toBe($request);

        $statusEvent = $filing->events()->where('event_type', 'status_changed')->latest('id')->first();
        expect($statusEvent->meta('customer_message'))->toBe($request['text'])
            ->and($statusEvent->meta('attachment_media_id'))->toBe($media->id)
            ->and($statusEvent->meta('note'))->toBe('Called them first, no answer.');

        $sentEvent = $filing->events()->where('event_type', 'client_request_sent')->sole();
        expect($sentEvent->payload_json['sent_to'])->toBe('dana@example.test')
            ->and($sentEvent->payload_json['text'])->toBe($request['text'])
            ->and($sentEvent->created_by)->toBe($this->admin->id)
            ->and($sentEvent->description())->toBe('Request sent to customer');
    });
});

describe('the request email', function () {
    it('goes out at once with a subject for each status', function (FilingStatus $status, string $lead) {
        clientRequestFixture($this->filing, $this->admin, $status);

        Mail::assertQueued(FilingActionRequested::class, 1);
        $mail = Mail::queued(FilingActionRequested::class)->first();

        expect($mail->hasTo('dana@example.test'))->toBeTrue()
            ->and($mail->afterCommit)->toBeTrue()
            ->and($mail->hasReplyTo('contact@eregister.com'))->toBeTrue();
        $mail->assertHasSubject("{$lead} {$this->prelim->name} — eRegister");
    })->with([
        'awaiting client' => [FilingStatus::AwaitingClient, 'We need information for your'],
        'awaiting e-signature' => [FilingStatus::AwaitingEsign, 'Please sign your'],
        'awaiting notary' => [FilingStatus::AwaitingNotary, 'Please notarize your'],
    ]);

    it('says who wrote, quotes the message as written and has one Answer here button', function () {
        clientRequestFixture($this->filing, $this->admin, message: "Please send:\n1. The signed contract\n<b>2.</b> # Your last invoice");

        $mail = Mail::queued(FilingActionRequested::class)->first();
        $html = $mail->render();

        $mail->assertSeeInHtml('Hi Dana,');
        $mail->assertSeeInHtml('Major at eRegister wrote:');
        $mail->assertSeeInHtml('Answer here');
        $mail->assertSeeInHtml(route('lien.filings.show', $this->filing));
        $mail->assertSeeInHtml('Maple Street Remodel');

        // Escaped, line breaks kept, and no Markdown heading from the "#".
        expect($html)->toContain('Please send:<br>1. The signed contract<br>&lt;b&gt;2.&lt;/b&gt; # Your last invoice')
            ->and($html)->not->toContain('<b>2.</b>')
            ->and($html)->not->toContain('<h1>Your last invoice');
        $mail->assertSeeInText("Please send:\n1. The signed contract");
    });

    it('attaches the PDF', function () {
        clientRequestFixture($this->filing, $this->admin, attachment: clientRequestPdf('lien-waiver-form.pdf'));

        $media = $this->filing->fresh()->getFirstMedia('requests');
        $mail = Mail::queued(FilingActionRequested::class)->first();

        expect($mail->hasAttachment($media))->toBeTrue()
            ->and($mail->attachments()[0]->toMailAttachment()->as)->toBe('lien-waiver-form.pdf');
        $mail->assertSeeInHtml('We attached lien-waiver-form.pdf to this email.');
    });

    it('leaves the internal note out', function () {
        $this->actingAs($this->admin);

        app(ChangeFilingStatus::class)->execute(
            filing: $this->filing,
            newStatus: FilingStatus::AwaitingClient,
            note: 'Customer seemed confused on the phone.',
            customerMessage: 'Please send the signed contract.',
        );

        Mail::queued(FilingActionRequested::class)->first()->assertDontSeeInHtml('Customer seemed confused on the phone.');
    });

    it('links an e-signature request straight to the signing page', function () {
        $request = clientRequestSignatureRequest($this->filing, $this->customer);

        clientRequestFixture($this->filing, $this->admin, FilingStatus::AwaitingEsign, message: 'Please sign the demand letter today.');

        $mail = Mail::queued(FilingActionRequested::class)->first();
        expect($mail->ctaLabel)->toBe('Sign now')
            ->and($mail->ctaUrl)->toContain('/esign/'.$request->public_id);
        $mail->assertSeeInHtml('Sign now');
    });

    it('sends nothing when the status changes without a message', function () {
        $this->actingAs($this->admin);
        $this->filing->transitionTo(FilingStatus::AwaitingClient);

        expect($this->filing->fresh()->client_request_json)->toBeNull();
        Mail::assertNotQueued(FilingActionRequested::class);
    });
});

describe('the reminders', function () {
    it('still start two days later', function () {
        $this->travelTo(now()->startOfMinute());

        clientRequestFixture($this->filing, $this->admin);

        $sequence = EmailSequence::whereMorphedTo('sequenceable', $this->filing)
            ->where('sequence_type', 'filing_action_reminder')
            ->sole();

        expect($sequence->trigger_status)->toBe('awaiting_client')
            ->and($sequence->next_send_at->diffInSeconds(now()->addMinutes(2880)))->toBeLessThan(5);
    });

    it('quote the request with the same button', function () {
        clientRequestFixture($this->filing, $this->admin, message: 'Please send the signed contract by Friday.');

        $sequence = EmailSequence::whereMorphedTo('sequenceable', $this->filing)
            ->where('sequence_type', 'filing_action_reminder')
            ->sole();

        $reminder = new FilingActionReminder($sequence, 1);

        expect($reminder->requestText)->toBe('Please send the signed contract by Friday.')
            ->and($reminder->ctaLabel)->toBe('Answer here')
            ->and($reminder->ctaUrl)->toBe(route('lien.filings.show', $this->filing));
        $reminder->assertSeeInHtml('Please send the signed contract by Friday.');
        $reminder->assertSeeInHtml('Here is what we asked for:');
        $reminder->assertDontSeeInHtml('Please check the request we emailed you and reply to it.');
    });
});

it('closes the request when the filing moves on, keeping the text', function () {
    clientRequestFixture($this->filing, $this->admin);

    Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
        ->set('newStatus', FilingStatus::ReadyToFile->value)
        ->call('updateStatus')
        ->assertHasNoErrors();

    $filing = $this->filing->fresh();
    expect($filing->client_request_json['resolved_at'])->not->toBeNull()
        ->and($filing->client_request_json['text'])->toBe('Please send the signed contract.')
        ->and($filing->openClientRequest())->toBeNull();
});

describe('the customer answers on the filing page', function () {
    beforeEach(function () {
        $this->filing = clientRequestFixture($this->filing, $this->admin, attachment: clientRequestPdf());

        $this->actingAs($this->customer);
        session(['current_business_id' => $this->business->id]);
    });

    it('shows the request, who sent it and when, and the attachment', function () {
        $sentAt = $this->filing->clientRequestSentAt()->eastern()->format('M j, Y \a\t g:i A T');

        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->assertSee('What we need from you')
            ->assertSee('Please send the signed contract.')
            ->assertSee("Sent by Major at eRegister on {$sentAt}.")
            ->assertSee('contract-form.pdf')
            ->assertSee('expiration=')
            ->assertSee('Answer the request')
            ->assertSee('Read the request below and answer it here.')
            ->assertDontSee('respond to our email')
            ->assertDontSee('check your email');
    });

    it('takes a typed answer, moves the filing to review and tells lien ops', function () {
        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('answer', 'Contract attached to my email. Invoice #4411 was sent on Oct 1.')
            ->call('submitAnswer')
            ->assertHasNoErrors()
            ->assertSee('Thanks. We have your answer and will keep going.')
            ->assertDontSee('What we need from you');

        $filing = $this->filing->fresh();
        expect($filing->status)->toBe(FilingStatus::NeedsReview)
            ->and($filing->client_request_json['resolved_at'])->not->toBeNull()
            ->and($filing->openClientRequest())->toBeNull();

        $response = $filing->events()->where('event_type', 'client_response')->sole();
        expect($response->payload_json['text'])->toBe('Contract attached to my email. Invoice #4411 was sent on Oct 1.')
            ->and($response->payload_json['media_id'])->toBeNull()
            ->and($response->created_by)->toBe($this->customer->id)
            ->and($response->description())->toBe('Customer answered');

        $sequence = EmailSequence::whereMorphedTo('sequenceable', $filing)
            ->where('sequence_type', 'filing_action_reminder')
            ->sole();
        expect($sequence->suppressed_at)->not->toBeNull()
            ->and($sequence->suppression_reason)->toBe('status_changed');

        Mail::assertQueued(ClientResponded::class, 1);
        $ops = Mail::queued(ClientResponded::class)->first();
        expect($ops->hasTo('liens@eregister.com'))->toBeTrue();
        $ops->assertHasSubject("Customer answered: Dana Drywall LLC — {$this->prelim->name}");
        $ops->assertSeeInHtml('Dana Drywall LLC');
        $ops->assertSeeInHtml('Maple Street Remodel');
        $ops->assertSeeInHtml('Please send the signed contract.');
        $ops->assertSeeInHtml('Invoice #4411 was sent on Oct 1.');
        $ops->assertSeeInHtml(route('admin.liens.show', $filing));
    });

    it('takes a file on its own', function () {
        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('answerFile', UploadedFile::fake()->create('notarized-copy.heic', 500, 'image/heic'))
            ->call('submitAnswer')
            ->assertHasNoErrors();

        $filing = $this->filing->fresh();
        $media = $filing->getFirstMedia('customer_uploads');

        expect($filing->status)->toBe(FilingStatus::NeedsReview)
            ->and($media)->not->toBeNull()
            ->and($media->file_name)->toBe('notarized-copy.heic')
            ->and($media->disk)->toBe('s3');

        $response = $filing->events()->where('event_type', 'client_response')->sole();
        expect($response->payload_json['text'])->toBeNull()
            ->and($response->payload_json['media_id'])->toBe($media->id)
            ->and($response->payload_json['file_name'])->toBe('notarized-copy.heic');

        Mail::queued(ClientResponded::class)->first()->assertSeeInHtml('notarized-copy.heic');
    });

    it('takes text and a file together', function () {
        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('answer', 'Here is the signed contract.')
            ->set('answerFile', clientRequestPdf('signed-contract.pdf'))
            ->call('submitAnswer')
            ->assertHasNoErrors();

        $filing = $this->filing->fresh();
        $response = $filing->events()->where('event_type', 'client_response')->sole();

        expect($response->payload_json['text'])->toBe('Here is the signed contract.')
            ->and($response->payload_json['media_id'])->toBe($filing->getFirstMedia('customer_uploads')->id);
    });

    it('needs an answer or a file', function () {
        $component = Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('answer', '   ')
            ->call('submitAnswer')
            ->assertHasErrors(['answer' => 'required_without']);

        expect($component->errors()->first('answer'))->toBe('Type your answer or attach a file.');
        expect($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingClient);
        expect($this->filing->events()->where('event_type', 'client_response')->exists())->toBeFalse();
        Mail::assertNotQueued(ClientResponded::class);
    });

    it('rejects other file types', function () {
        $component = Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('answerFile', UploadedFile::fake()->create('setup.exe', 10, 'application/x-msdownload'))
            ->call('submitAnswer')
            ->assertHasErrors(['answerFile' => 'mimes']);

        expect($component->errors()->first('answerFile'))->toBe('Attach a PDF, JPG, PNG or HEIC file.');
        expect($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingClient);
    });

    it('takes files up to 20 MB and no larger', function () {
        // Livewire's temporary upload limit is raised to match (config/livewire.php).
        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('answerFile', UploadedFile::fake()->create('scan.pdf', 20481, 'application/pdf'))
            ->assertHasErrors('answerFile');

        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('answerFile', UploadedFile::fake()->create('scan.pdf', 20480, 'application/pdf'))
            ->assertHasNoErrors()
            ->call('submitAnswer')
            ->assertHasNoErrors();

        expect($this->filing->fresh()->status)->toBe(FilingStatus::NeedsReview);
    });

    it('works from Awaiting Notary too', function () {
        $filing = LienFiling::factory()->forProject($this->project)->paid()->create([
            'document_type_id' => $this->prelim->id,
            'created_by_user_id' => $this->customer->id,
        ]);
        $filing = clientRequestFixture($filing, $this->admin, FilingStatus::AwaitingNotary, message: 'Please get this notarized and upload it.');

        $this->actingAs($this->customer);

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->assertSee('Follow the request below. You can upload the notarized copy here.')
            ->set('answerFile', clientRequestPdf('notarized.pdf'))
            ->call('submitAnswer')
            ->assertHasNoErrors();

        expect($filing->fresh()->status)->toBe(FilingStatus::NeedsReview);
    });

    it('does not let someone outside the business answer', function () {
        $outsider = User::factory()->create();
        $otherBusiness = Business::factory()->onboarded()->withLienOnboarding()->create();
        $otherBusiness->users()->attach($outsider, ['role' => 'owner']);

        $component = Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('answer', 'Not my filing.');

        $this->actingAs($outsider);
        session(['current_business_id' => $otherBusiness->id]);

        $component->call('submitAnswer')->assertForbidden();

        expect($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingClient)
            ->and($this->filing->events()->where('event_type', 'client_response')->exists())->toBeFalse();
        Mail::assertNotQueued(ClientResponded::class);

        expect($this->get(route('lien.filings.show', $this->filing))->status())->toBeIn([403, 404]);
    });

    it('sends no ops email when the ops inbox is blank', function () {
        config(['lien.notifications.ops_email' => '']);

        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('answer', 'Done.')
            ->call('submitAnswer')
            ->assertHasNoErrors();

        expect($this->filing->fresh()->status)->toBe(FilingStatus::NeedsReview);
        Mail::assertNotQueued(ClientResponded::class);
    });
});

it('shows no answer panel on a filing without a stored request', function () {
    // Moved to Awaiting Client before requests were stored.
    $this->filing->update(['status' => FilingStatus::AwaitingClient]);

    $this->actingAs($this->customer);
    session(['current_business_id' => $this->business->id]);

    Livewire::test(FilingShow::class, ['filing' => $this->filing])
        ->assertDontSee('What we need from you')
        ->assertDontSee('Read the request below')
        ->assertSee('Please check the request we emailed you and reply to it.')
        ->set('answer', 'Here you go.')
        ->call('submitAnswer')
        ->assertSee('This request is already closed.');

    expect($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingClient)
        ->and($this->filing->events()->where('event_type', 'client_response')->exists())->toBeFalse();
});

it('shows the request and the answers on the admin filing page', function () {
    $filing = clientRequestFixture($this->filing, $this->admin, attachment: clientRequestPdf('contract-form.pdf'));

    $this->actingAs($this->customer);
    session(['current_business_id' => $this->business->id]);

    Livewire::test(FilingShow::class, ['filing' => $filing])
        ->set('answer', 'Signed contract attached.')
        ->set('answerFile', clientRequestPdf('signed-contract.pdf'))
        ->call('submitAnswer')
        ->assertHasNoErrors();

    $this->actingAs($this->admin);

    Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
        ->assertSee('Requests and answers')
        ->assertSee('Latest request')
        ->assertSee('Please send the signed contract.')
        ->assertSee('contract-form.pdf')
        ->assertSee('Signed contract attached.')
        ->assertSee('signed-contract.pdf')
        ->assertSee('Request sent to customer')
        ->assertSee('to dana@example.test')
        ->assertSee('Customer answered');
});

describe('the waiting-status copy on the filing page', function () {
    it('points at the request on the page, not at an email', function (FilingStatus $status, string $description, string $next) {
        expect($status->userDescription())->toBe($description)
            ->and($status->whatsNext())->toBe($next);

        foreach ([$status->userDescription(), $status->whatsNext()] as $copy) {
            expect($copy)->not->toContain('respond to our email')
                ->and($copy)->not->toContain('check your email')
                ->and($copy)->not->toContain('sent to your email');
        }
    })->with([
        'awaiting client' => [FilingStatus::AwaitingClient, "We've asked you for something and need it before we can continue.", 'Read the request below and answer it here.'],
        'awaiting notary' => [FilingStatus::AwaitingNotary, 'Your document needs to be notarized.', 'Follow the request below. You can upload the notarized copy here.'],
        'awaiting e-signature' => [FilingStatus::AwaitingEsign, 'Your document is ready for your e-signature.', 'Use the button in the email or on this page to sign.'],
    ]);
});
