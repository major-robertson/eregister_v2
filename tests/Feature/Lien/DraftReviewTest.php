<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Mail\ClientResponded;
use App\Mail\FilingActionReminder;
use App\Mail\FilingActionRequested;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/*
| EREG-140: an admin uploads a draft (or copies the generated document) and
| sends it for review; the customer sees the PDF on the filing page and
| approves it or asks for changes.
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

if (! function_exists('draftReviewPdf')) {
    /** A small upload that reads as a real PDF (the drafts collection checks the type). */
    function draftReviewPdf(string $name = 'prelim-draft.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n% draft for review\n");
    }
}

if (! function_exists('draftReviewUpload')) {
    /** Upload a draft as the admin through the filing page. */
    function draftReviewUpload(LienFiling $filing, User $admin, string $name = 'prelim-draft.pdf'): Media
    {
        test()->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->set('draftFile', draftReviewPdf($name))
            ->call('uploadDraft')
            ->assertHasNoErrors();

        return $filing->fresh()->draftVersions()->first();
    }
}

if (! function_exists('draftReviewSend')) {
    /** Upload a draft and send it for review as the admin. */
    function draftReviewSend(LienFiling $filing, User $admin, bool $needsSignature = false, string $name = 'prelim-draft.pdf'): LienFiling
    {
        draftReviewUpload($filing, $admin, $name);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->call('confirmSendDraftForReview')
            ->set('draftNeedsSignature', $needsSignature)
            ->call('sendDraftForReview')
            ->assertHasNoErrors();

        return $filing->fresh();
    }
}

if (! function_exists('draftReviewAsCustomer')) {
    function draftReviewAsCustomer(User $customer, Business $business): void
    {
        test()->actingAs($customer);
        session(['current_business_id' => $business->id]);
    }
}

describe('the admin draft card', function () {
    beforeEach(function () {
        $this->actingAs($this->admin);
    });

    it('takes a PDF as the first version', function () {
        $this->travelTo(now()->startOfMinute());

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->assertSee('Draft for review')
            ->assertSee('No draft yet.')
            ->set('draftFile', draftReviewPdf())
            ->call('uploadDraft')
            ->assertHasNoErrors()
            ->assertSee('Draft v1 uploaded.')
            ->assertSee('v1')
            ->assertSee('prelim-draft.pdf')
            ->assertSee('Major Robertson')
            ->assertSee(now()->eastern()->format('M j, Y g:i A T'))
            ->assertSee('expiration=');

        $draft = $this->filing->fresh()->draftVersions()->sole();

        expect($draft->collection_name)->toBe('drafts')
            ->and($draft->disk)->toBe('s3')
            ->and($draft->mime_type)->toBe('application/pdf')
            ->and($draft->getCustomProperty('version'))->toBe(1)
            ->and($draft->getCustomProperty('uploaded_by_user_id'))->toBe($this->admin->id)
            ->and($draft->getCustomProperty('source'))->toBe('upload');
        Storage::disk('s3')->assertExists($draft->getPathRelativeToRoot());

        // Uploading alone tells nobody and leaves the status alone.
        expect($this->filing->fresh()->status)->toBe(FilingStatus::Paid);
        Mail::assertNothingQueued();
    });

    it('keeps every version, newest first', function () {
        draftReviewUpload($this->filing, $this->admin, 'first-try.pdf');
        draftReviewUpload($this->filing, $this->admin, 'second-try.pdf');

        $drafts = $this->filing->fresh()->draftVersions();

        expect($drafts)->toHaveCount(2)
            ->and($drafts->pluck('file_name')->all())->toBe(['second-try.pdf', 'first-try.pdf'])
            ->and($drafts->map(fn (Media $draft) => $draft->getCustomProperty('version'))->all())->toBe([2, 1]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertSeeInOrder(['Draft for review', 'v2', 'second-try.pdf', 'v1', 'first-try.pdf']);
    });

    it('only takes a PDF', function () {
        $component = Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('draftFile', UploadedFile::fake()->create('photo.png', 100, 'image/png'))
            ->call('uploadDraft')
            ->assertHasErrors(['draftFile' => 'mimes']);

        expect($component->errors()->first('draftFile'))->toBe('The draft must be a PDF.');
        expect($this->filing->fresh()->draftVersions())->toBeEmpty();
    });

    it('asks for a file before uploading', function () {
        $component = Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->call('uploadDraft')
            ->assertHasErrors(['draftFile' => 'required']);

        expect($component->errors()->first('draftFile'))->toBe('Choose the PDF to upload.');
    });

    it('refuses a draft over 20 MB', function () {
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('draftFile', UploadedFile::fake()->create('huge.pdf', 20481, 'application/pdf'))
            ->assertHasErrors('draftFile');

        expect($this->filing->fresh()->draftVersions())->toBeEmpty();
    });

    it('is only for staff who can change the status', function () {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo(['lien.view']);
        $this->actingAs($viewer);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->assertDontSee('Draft for review')
            ->set('draftFile', draftReviewPdf())
            ->call('uploadDraft')
            ->assertForbidden();

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->call('confirmSendDraftForReview')
            ->assertForbidden();

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing])
            ->set('draftMessage', 'Please review it.')
            ->call('sendDraftForReview')
            ->assertForbidden();

        expect($this->filing->fresh()->draftVersions())->toBeEmpty()
            ->and($this->filing->fresh()->status)->toBe(FilingStatus::Paid);
    });
});

describe('sending a draft for review', function () {
    beforeEach(function () {
        $this->actingAs($this->admin);
    });

    it('prefills the message for the customer', function () {
        draftReviewUpload($this->filing, $this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->call('confirmSendDraftForReview')
            ->assertSet('showSendDraftModal', true)
            ->assertSet('draftMessage', "Please review the attached draft of your {$this->prelim->name}. If everything is correct, approve it. If something needs to change, tell us what.")
            ->assertSet('draftNeedsSignature', false)
            ->assertSee('Send the draft for review?')
            ->assertSee('This document will need the customer&#039;s signature afterwards', false)
            ->assertSee('Dana Builder &lt;dana@example.test&gt;', false)
            ->assertSee("Please review the draft of your {$this->prelim->name} — eRegister");
    });

    it('needs a message', function () {
        draftReviewUpload($this->filing, $this->admin);

        $component = Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->call('confirmSendDraftForReview')
            ->set('draftMessage', '  ')
            ->call('sendDraftForReview')
            ->assertHasErrors(['draftMessage' => 'required']);

        expect($component->errors()->first('draftMessage'))->toBe('Write the message for the customer. It is emailed to them with the draft.');
        expect($this->filing->fresh()->status)->toBe(FilingStatus::Paid);
        Mail::assertNotQueued(FilingActionRequested::class);
    });

    it('opens a review request with the newest draft and moves the filing to Awaiting Client', function () {
        draftReviewUpload($this->filing, $this->admin, 'first-try.pdf');
        $newest = draftReviewUpload($this->filing, $this->admin, 'second-try.pdf');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->call('confirmSendDraftForReview')
            ->set('draftMessage', "Please check the owner's name.\nThen approve it.")
            ->set('draftNeedsSignature', true)
            ->call('sendDraftForReview')
            ->assertHasNoErrors()
            ->assertSet('showSendDraftModal', false)
            ->assertSee('Draft v2 sent for review. We emailed dana@example.test.');

        $filing = $this->filing->fresh();
        $request = $filing->client_request_json;

        expect($filing->status)->toBe(FilingStatus::AwaitingClient)
            ->and($request['type'])->toBe('review')
            ->and($request['draft_media_id'])->toBe($newest->id)
            ->and($request['needs_signature'])->toBeTrue()
            ->and($request['text'])->toBe("Please check the owner's name.\nThen approve it.")
            ->and($request['status'])->toBe('awaiting_client')
            ->and($request['requested_by_user_id'])->toBe($this->admin->id)
            ->and($request['resolved_at'])->toBeNull()
            ->and($filing->openReviewRequest())->toBe($request)
            ->and($filing->reviewDraft()->id)->toBe($newest->id);

        $sent = $filing->events()->where('event_type', 'draft_sent_for_review')->sole();
        expect($sent->payload_json)->toEqual([
            'media_id' => $newest->id,
            'version' => 2,
            'file_name' => 'second-try.pdf',
            'needs_signature' => true,
        ])
            ->and($sent->created_by)->toBe($this->admin->id)
            ->and($sent->description())->toBe('Draft sent for review');

        $requestSent = $filing->events()->where('event_type', 'client_request_sent')->sole();
        expect($requestSent->payload_json['type'])->toBe('review')
            ->and($requestSent->payload_json['sent_to'])->toBe('dana@example.test');

        // The reminders start as for any request, with the review button.
        $sequence = EmailSequence::whereMorphedTo('sequenceable', $filing)
            ->where('sequence_type', 'filing_action_reminder')
            ->sole();
        expect($sequence->trigger_status)->toBe('awaiting_client');

        $reminder = new FilingActionReminder($sequence, 1);
        expect($reminder->ctaLabel)->toBe('Review the draft')
            ->and($reminder->requestText)->toBe("Please check the owner's name.\nThen approve it.");
    });

    it('emails the customer at once with the draft attached and a Review the draft button', function () {
        $filing = draftReviewSend($this->filing, $this->admin, name: 'prelim-notice-draft.pdf');
        $draft = $filing->reviewDraft();

        Mail::assertQueued(FilingActionRequested::class, 1);
        $mail = Mail::queued(FilingActionRequested::class)->first();

        expect($mail->hasTo('dana@example.test'))->toBeTrue()
            ->and($mail->afterCommit)->toBeTrue()
            ->and($mail->hasAttachment($draft))->toBeTrue()
            ->and($mail->attachments()[0]->toMailAttachment()->as)->toBe('prelim-notice-draft.pdf')
            ->and($mail->ctaLabel)->toBe('Review the draft')
            ->and($mail->ctaUrl)->toBe(route('lien.filings.show', $filing));
        $mail->assertHasSubject("Please review the draft of your {$this->prelim->name} — eRegister");
        $mail->assertSeeInHtml('Major at eRegister wrote:');
        $mail->assertSeeInHtml("Please review the attached draft of your {$this->prelim->name}.");
        $mail->assertSeeInHtml('We attached prelim-notice-draft.pdf to this email.');
        $mail->assertSeeInHtml('Review the draft');
        $mail->assertDontSeeInHtml('Answer here');
    });

    it('cannot be sent while the filing waits on another request', function () {
        draftReviewUpload($this->filing, $this->admin);

        app(ChangeFilingStatus::class)->execute(
            filing: $this->filing->fresh(),
            newStatus: FilingStatus::AwaitingClient,
            customerMessage: 'Please send the signed contract.',
        );
        Mail::fake();

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertDontSee("Waiting on the customer's review.", false)
            ->assertSee("A draft can't be sent for review from the “Awaiting Client” status.", false)
            ->set('draftMessage', 'Please review it.')
            ->call('sendDraftForReview')
            // The flash message, escaped.
            ->assertSee('A draft can&#039;t be sent for review from the “Awaiting Client” status.', false);

        $filing = $this->filing->fresh();
        expect($filing->client_request_json['type'] ?? null)->toBeNull()
            ->and($filing->events()->where('event_type', 'draft_sent_for_review')->exists())->toBeFalse();
        Mail::assertNotQueued(FilingActionRequested::class);
    });

    it('says the review is open while it waits on the customer', function () {
        $filing = draftReviewSend($this->filing, $this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee("Waiting on the customer's review.", false)
            ->assertSee('Sent for review')
            ->assertSee('Open request')
            ->assertSee('Draft for review: v1');
    });
});

describe('the customer review panel', function () {
    it('shows the draft, Approve and Request changes for a review request', function () {
        $filing = draftReviewSend($this->filing, $this->admin, name: 'prelim-notice-draft.pdf');
        $draft = $filing->reviewDraft();
        draftReviewAsCustomer($this->customer, $this->business);

        $component = Livewire::test(FilingShow::class, ['filing' => $filing])
            ->assertSee('Review your draft')
            ->assertSee("Please review the attached draft of your {$this->prelim->name}.")
            ->assertSee('Sent by Major at eRegister')
            ->assertSee('Your draft is ready for review.')
            ->assertSee('Read the draft above. Approve it, or tell us what to change.')
            ->assertSee("/portal/liens/filings/{$filing->public_id}/drafts/{$draft->id}")
            ->assertSee('signature=')
            ->assertSee('Download the draft (prelim-notice-draft.pdf)')
            ->assertSee('I have read the whole document and the information in it is correct.')
            ->assertSee('Approve the draft')
            ->assertSee('What should we change?')
            ->assertSee('Request changes')
            ->assertDontSee('What we need from you')
            ->assertDontSee('Upload your signed document')
            ->assertDontSee('Answer the request');

        expect(substr_count($component->html(), '<iframe'))->toBe(1);
    });

    it('is not shown for any other request', function () {
        $this->actingAs($this->admin);
        app(ChangeFilingStatus::class)->execute(
            filing: $this->filing,
            newStatus: FilingStatus::AwaitingClient,
            customerMessage: 'Please send the signed contract.',
        );
        draftReviewAsCustomer($this->customer, $this->business);

        Livewire::test(FilingShow::class, ['filing' => $this->filing->fresh()])
            ->assertSee('What we need from you')
            ->assertDontSee('Review your draft')
            ->assertDontSee('Approve the draft')
            ->assertDontSee('Your draft is ready for review.');
    });

    it('is gone once the filing has moved on', function () {
        $filing = draftReviewSend($this->filing, $this->admin);

        $this->actingAs($this->admin);
        $filing->transitionTo(FilingStatus::NeedsReview);
        draftReviewAsCustomer($this->customer, $this->business);

        Livewire::test(FilingShow::class, ['filing' => $filing->fresh()])
            ->assertDontSee('Review your draft')
            ->set('approveConfirmed', true)
            ->call('approveDraft')
            ->assertSee('This review is already closed.');

        expect($filing->fresh()->status)->toBe(FilingStatus::NeedsReview)
            ->and($filing->events()->where('event_type', 'draft_approved')->exists())->toBeFalse();
    });
});

describe('the signed draft link', function () {
    beforeEach(function () {
        $this->reviewFiling = draftReviewSend($this->filing, $this->admin);
        $this->draft = $this->reviewFiling->reviewDraft();
        $this->signedUrl = URL::temporarySignedRoute('lien.filings.drafts.show', now()->addHour(), [
            'filing' => $this->reviewFiling,
            'media' => $this->draft->id,
        ]);
    });

    it('shows the draft inline to the filing\'s business', function () {
        draftReviewAsCustomer($this->customer, $this->business);

        $response = $this->get($this->signedUrl);

        $response->assertOk();
        expect($response->headers->get('content-type'))->toContain('application/pdf')
            ->and($response->headers->get('content-disposition'))->toContain('inline')
            ->and(substr($response->streamedContent(), 0, 4))->toBe('%PDF');
    });

    it('needs the signature', function () {
        draftReviewAsCustomer($this->customer, $this->business);

        $this->get(route('lien.filings.drafts.show', [$this->reviewFiling, $this->draft->id]))->assertForbidden();
    });

    it('is closed to other businesses', function () {
        $outsider = User::factory()->create();
        $otherBusiness = Business::factory()->onboarded()->withLienOnboarding()->create();
        $otherBusiness->users()->attach($outsider, ['role' => 'owner']);
        draftReviewAsCustomer($outsider, $otherBusiness);

        expect($this->get($this->signedUrl)->status())->toBeIn([403, 404]);
    });

    it('only serves that filing\'s drafts', function () {
        $upload = $this->reviewFiling->addMedia(draftReviewPdf('answer.pdf'))->toMediaCollection('customer_uploads');
        draftReviewAsCustomer($this->customer, $this->business);

        $url = URL::temporarySignedRoute('lien.filings.drafts.show', now()->addHour(), [
            'filing' => $this->reviewFiling,
            'media' => $upload->id,
        ]);

        $this->get($url)->assertNotFound();
    });
});

describe('approving the draft', function () {
    beforeEach(function () {
        $this->reviewFiling = draftReviewSend($this->filing, $this->admin);
        Mail::fake();
        draftReviewAsCustomer($this->customer, $this->business);
    });

    it('needs the box ticked', function () {
        $component = Livewire::test(FilingShow::class, ['filing' => $this->reviewFiling])
            ->call('approveDraft')
            ->assertHasErrors(['approveConfirmed' => 'accepted']);

        expect($component->errors()->first('approveConfirmed'))->toBe('Tick the box to confirm you have read the whole document.');
        expect($this->reviewFiling->fresh()->status)->toBe(FilingStatus::AwaitingClient)
            ->and($this->reviewFiling->events()->where('event_type', 'draft_approved')->exists())->toBeFalse();
        Mail::assertNotQueued(ClientResponded::class);
    });

    it('records the approval, closes the request and moves the filing to Ready to File', function () {
        $this->travelTo(now()->startOfSecond());
        $draft = $this->reviewFiling->reviewDraft();

        Livewire::test(FilingShow::class, ['filing' => $this->reviewFiling])
            ->set('approveConfirmed', true)
            ->call('approveDraft')
            ->assertHasNoErrors()
            ->assertSee('Thanks. You approved the draft.')
            ->assertSee('We will take it from here and email you when there is an update.')
            ->assertDontSee('Review your draft');

        $filing = $this->reviewFiling->fresh();
        expect($filing->status)->toBe(FilingStatus::ReadyToFile)
            ->and($filing->client_request_json['resolved_at'])->not->toBeNull()
            ->and($filing->openClientRequest())->toBeNull()
            ->and($filing->customerRepliedAt())->not->toBeNull();

        $approval = $filing->events()->where('event_type', 'draft_approved')->sole();
        expect($approval->payload_json)->toEqual([
            'media_id' => $draft->id,
            'version' => 1,
            'file_name' => 'prelim-draft.pdf',
            'approved_by_user_id' => $this->customer->id,
            'approved_at' => now()->toIso8601String(),
            'needs_signature' => false,
        ])
            ->and($approval->created_by)->toBe($this->customer->id)
            ->and($approval->description())->toBe('Draft approved');

        $sequence = EmailSequence::whereMorphedTo('sequenceable', $filing)
            ->where('sequence_type', 'filing_action_reminder')
            ->sole();
        expect($sequence->suppressed_at)->not->toBeNull();

        Mail::assertQueued(ClientResponded::class, 1);
        $ops = Mail::queued(ClientResponded::class)->first();
        expect($ops->hasTo('liens@eregister.com'))->toBeTrue()
            ->and($ops->afterCommit)->toBeTrue();
        $ops->assertHasSubject("Draft approved: Dana Drywall LLC — {$this->prelim->name}");
        $ops->assertSeeInHtml("Dana Builder approved version 1 of the draft of their {$this->prelim->name}. The filing is now in Ready to File.");
        $ops->assertSeeInHtml('v1, prelim-draft.pdf');
        $ops->assertDontSeeInHtml('It needs their signature next.');
        $ops->assertDontSeeInHtml('They answered');
        $ops->assertSeeInHtml(route('admin.liens.show', $filing));
    });

    it('goes to Needs Review when the document needs their signature', function () {
        $filing = LienFiling::factory()->forProject($this->project)->paid()->create([
            'document_type_id' => $this->prelim->id,
            'created_by_user_id' => $this->customer->id,
        ]);
        $filing = draftReviewSend($filing, $this->admin, needsSignature: true);
        Mail::fake();
        draftReviewAsCustomer($this->customer, $this->business);

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->set('approveConfirmed', true)
            ->call('approveDraft')
            ->assertHasNoErrors()
            ->assertSee('Next we will send it to you to sign.');

        $filing = $filing->fresh();
        expect($filing->status)->toBe(FilingStatus::NeedsReview)
            ->and($filing->events()->where('event_type', 'draft_approved')->sole()->payload_json['needs_signature'])->toBeTrue();

        // No "please sign" reminders before anything has been sent to sign.
        expect(EmailSequence::whereMorphedTo('sequenceable', $filing)
            ->where('sequence_type', 'filing_action_reminder')
            ->whereNull('suppressed_at')
            ->exists())->toBeFalse();

        $ops = Mail::queued(ClientResponded::class)->sole();
        $ops->assertSeeInHtml('The filing is now in Needs Review.');
        $ops->assertSeeInHtml('It needs their signature next. Send the signature request from the filing page.');
    });

    it('is only for members of the filing\'s business', function () {
        $outsider = User::factory()->create();
        $otherBusiness = Business::factory()->onboarded()->withLienOnboarding()->create();
        $otherBusiness->users()->attach($outsider, ['role' => 'owner']);

        $component = Livewire::test(FilingShow::class, ['filing' => $this->reviewFiling])
            ->set('approveConfirmed', true);

        draftReviewAsCustomer($outsider, $otherBusiness);

        $component->call('approveDraft')->assertForbidden();

        expect($this->reviewFiling->fresh()->status)->toBe(FilingStatus::AwaitingClient)
            ->and($this->reviewFiling->events()->where('event_type', 'draft_approved')->exists())->toBeFalse();
        Mail::assertNotQueued(ClientResponded::class);
    });
});

describe('asking for changes', function () {
    beforeEach(function () {
        $this->reviewFiling = draftReviewSend($this->filing, $this->admin);
        Mail::fake();
        draftReviewAsCustomer($this->customer, $this->business);
    });

    it('needs the changes written out', function () {
        $component = Livewire::test(FilingShow::class, ['filing' => $this->reviewFiling])
            ->set('changes', '   ')
            ->set('changesFile', draftReviewPdf('marked-up.pdf'))
            ->call('requestDraftChanges')
            ->assertHasErrors(['changes' => 'required']);

        expect($component->errors()->first('changes'))->toBe('Tell us what needs to change.');
        expect($this->reviewFiling->fresh()->status)->toBe(FilingStatus::AwaitingClient)
            ->and($this->reviewFiling->fresh()->getMedia('customer_uploads'))->toBeEmpty();
        Mail::assertNotQueued(ClientResponded::class);
    });

    it('keeps them under 5,000 characters', function () {
        $component = Livewire::test(FilingShow::class, ['filing' => $this->reviewFiling])
            ->set('changes', str_repeat('a', 5001))
            ->call('requestDraftChanges')
            ->assertHasErrors(['changes' => 'max']);

        expect($component->errors()->first('changes'))->toBe('Keep it under 5,000 characters.');
    });

    it('records the changes with a file and moves the filing to Needs Review', function () {
        $draft = $this->reviewFiling->reviewDraft();

        Livewire::test(FilingShow::class, ['filing' => $this->reviewFiling])
            ->set('changes', "The owner is Maple Holdings LLC, not Maple LLC.\nThe amount is \$12,400.")
            ->set('changesFile', draftReviewPdf('marked-up.pdf'))
            ->call('requestDraftChanges')
            ->assertHasNoErrors()
            ->assertSee('Thanks. We have your changes.')
            ->assertSee('We will update the draft and send it back to you to review.')
            ->assertDontSee('Review your draft');

        $filing = $this->reviewFiling->fresh();
        $upload = $filing->getFirstMedia('customer_uploads');

        expect($filing->status)->toBe(FilingStatus::NeedsReview)
            ->and($filing->openClientRequest())->toBeNull()
            ->and($upload->file_name)->toBe('marked-up.pdf')
            ->and($upload->getCustomProperty('kind'))->toBe('draft_changes');

        $changes = $filing->events()->where('event_type', 'draft_changes_requested')->sole();
        expect($changes->payload_json)->toEqual([
            'text' => "The owner is Maple Holdings LLC, not Maple LLC.\nThe amount is \$12,400.",
            'media_id' => $upload->id,
            'file_name' => 'marked-up.pdf',
            'draft_media_id' => $draft->id,
            'version' => 1,
            'draft_file_name' => 'prelim-draft.pdf',
        ])
            ->and($changes->created_by)->toBe($this->customer->id)
            ->and($changes->description())->toBe('Changes requested');

        $ops = Mail::queued(ClientResponded::class)->sole();
        expect($ops->hasTo('liens@eregister.com'))->toBeTrue();
        $ops->assertHasSubject("Changes requested: Dana Drywall LLC — {$this->prelim->name}");
        $ops->assertSeeInHtml("Dana Builder asked for changes to version 1 of the draft of their {$this->prelim->name}. The filing is now in Needs Review.");
        $ops->assertSeeInHtml('They asked for:');
        $ops->assertSeeInHtml('The owner is Maple Holdings LLC, not Maple LLC.<br>The amount is $12,400.', false);
        $ops->assertSeeInHtml('marked-up.pdf');
    });

    it('works without a file', function () {
        Livewire::test(FilingShow::class, ['filing' => $this->reviewFiling])
            ->set('changes', 'Use our new address.')
            ->call('requestDraftChanges')
            ->assertHasNoErrors();

        $changes = $this->reviewFiling->events()->where('event_type', 'draft_changes_requested')->sole();
        expect($changes->payload_json['media_id'])->toBeNull()
            ->and($this->reviewFiling->fresh()->status)->toBe(FilingStatus::NeedsReview);
    });

    it('is only for members of the filing\'s business', function () {
        $outsider = User::factory()->create();
        $otherBusiness = Business::factory()->onboarded()->withLienOnboarding()->create();
        $otherBusiness->users()->attach($outsider, ['role' => 'owner']);

        $component = Livewire::test(FilingShow::class, ['filing' => $this->reviewFiling])
            ->set('changes', 'Not my filing.');

        draftReviewAsCustomer($outsider, $otherBusiness);

        $component->call('requestDraftChanges')->assertForbidden();

        expect($this->reviewFiling->fresh()->status)->toBe(FilingStatus::AwaitingClient);
        Mail::assertNotQueued(ClientResponded::class);
    });
});

describe('the admin filing page after the review', function () {
    it('shows the approval with the version approved', function () {
        $filing = draftReviewSend($this->filing, $this->admin, needsSignature: true);
        draftReviewAsCustomer($this->customer, $this->business);

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->set('approveConfirmed', true)
            ->call('approveDraft')
            ->assertHasNoErrors();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->assertSee('Requests and answers')
            ->assertSee('Latest request')
            ->assertSee('Draft for review: v1')
            ->assertSee('Customer approved the draft')
            ->assertSee('Approved version 1')
            ->assertSee('Needs their signature next')
            ->assertSee('Approved')
            ->assertSee('Draft sent for review')
            ->assertSee('Draft approved');
    });

    it('shows the change request text and file', function () {
        $filing = draftReviewSend($this->filing, $this->admin);
        draftReviewAsCustomer($this->customer, $this->business);

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->set('changes', 'The job number is 4411.')
            ->set('changesFile', draftReviewPdf('marked-up.pdf'))
            ->call('requestDraftChanges')
            ->assertHasNoErrors();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->assertSee('Customer asked for changes')
            ->assertSee('On version 1')
            ->assertSee('The job number is 4411.')
            ->assertSee('marked-up.pdf')
            ->assertSee('Changes to the draft')
            ->assertSee('Changes requested');

        // A new version can go out again from Needs Review.
        draftReviewUpload($filing, $this->admin, 'fixed.pdf');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->call('confirmSendDraftForReview')
            ->call('sendDraftForReview')
            ->assertHasNoErrors();

        expect($filing->fresh()->status)->toBe(FilingStatus::AwaitingClient)
            ->and($filing->fresh()->reviewDraft()->file_name)->toBe('fixed.pdf');
    });
});

describe('the generated document', function () {
    beforeEach(function () {
        LienDocumentRegistry::flush();
    });

    afterEach(fn () => LienDocumentRegistry::flush());

    it('can be copied into the drafts as a new version', function () {
        $project = LienProject::factory()->forBusiness(Business::factory()->create([
            'responsible_people' => [['user_id' => 1, 'name' => 'Andrew Redden', 'title' => 'Director of Operations', 'can_sign_liens' => true]],
        ]))->create([
            'claimant_type' => ClaimantType::Subcontractor,
            'hired_by' => 'direct_contractor',
            'jobsite_address1' => '10451 Bells Ferry Rd',
            'jobsite_city' => 'Canton',
            'jobsite_state' => 'GA',
            'jobsite_zip' => '30114',
            'jobsite_county' => 'Cherokee',
            'legal_description' => 'Land Lots 135 and 136, 15th District',
            'apn' => '15N07 047 A',
            'first_furnish_date' => '2026-04-01',
            'last_furnish_date' => '2026-05-31',
            'base_contract_amount_cents' => 6329000,
            'payments_received_cents' => 0,
        ]);

        foreach ([
            [PartyRole::Claimant, 'Andrew Redden', 'Yard-Nique Inc', '10014 Chapel Hill Rd', 'Morrisville', 'NC', '27560'],
            [PartyRole::Owner, 'JO-ASH Bells Ferry LLC', 'JO-ASH Bells Ferry LLC', '1140 Avenue of the Americas Ste 1701', 'New York', 'NY', '10036'],
            [PartyRole::Gc, 'Jonathan Kaplan', 'United Group', '1609 6th Ave 3rd Fl', 'Troy', 'NY', '12180'],
        ] as [$role, $name, $company, $address1, $city, $state, $zip]) {
            LienParty::create([
                'business_id' => $project->business_id,
                'project_id' => $project->id,
                'role' => $role,
                'name' => $name,
                'company_name' => $company,
                'address1' => $address1,
                'city' => $city,
                'state' => $state,
                'zip' => $zip,
            ]);
        }

        $filing = LienFiling::factory()->forProject($project)->paid()->create([
            'document_type_id' => LienDocumentType::where('slug', 'mechanics_lien')->firstOrFail()->id,
            'amount_claimed_cents' => 6329000,
            'description_of_work' => 'Landscaping and irrigation',
        ]);

        // A hand-built draft first, so the copy is version 2.
        draftReviewUpload($filing, $this->admin, 'hand-built.pdf');

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->assertSee('Use the generated document')
            ->call('useGeneratedDraft')
            ->assertSee('The generated document was saved as draft v2.')
            ->assertSee('From the generated document');

        $draft = $filing->fresh()->draftVersions()->first();

        expect($draft->getCustomProperty('version'))->toBe(2)
            ->and($draft->getCustomProperty('source'))->toBe('generated')
            ->and($draft->getCustomProperty('uploaded_by_user_id'))->toBe($this->admin->id)
            ->and($draft->file_name)->toStartWith('Yard-Nique-Inc-Claim-of-Lien')
            ->and($draft->mime_type)->toBe('application/pdf')
            ->and(substr(Storage::disk('s3')->get($draft->getPathRelativeToRoot()), 0, 4))->toBe('%PDF');
    });

    it('is not offered without a generated document', function () {
        $this->actingAs($this->admin);
        $demand = LienFiling::factory()->forProject($this->project)->paid()->create([
            'document_type_id' => LienDocumentType::where('slug', 'demand_letter')->firstOrFail()->id,
            'created_by_user_id' => $this->customer->id,
        ]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $demand])
            ->assertSee('Draft for review')
            ->assertDontSee('Use the generated document')
            ->call('useGeneratedDraft')
            ->assertSee('This filing has no generated document.');

        expect($demand->fresh()->draftVersions())->toBeEmpty();
    });
});
