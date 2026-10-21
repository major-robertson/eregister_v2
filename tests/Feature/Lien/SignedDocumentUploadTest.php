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
use App\Mail\UploadReceived;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
| EREG-129: while a filing waits on the customer (Awaiting Client /
| E-Signature / Notary), the filing page has an upload box for the signed or
| notarized document. Uploading it moves the filing to Needs Review, tells
| lien ops, confirms to the customer, and lists the file for staff.
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

if (! function_exists('signedUploadFile')) {
    /**
     * A small upload whose bytes really are the given type (the upload box
     * checks the content, not just the name).
     */
    function signedUploadFile(string $type = 'pdf', ?string $name = null): File
    {
        [$defaultName, $content] = match ($type) {
            'pdf' => ['notarized-lien.pdf', "%PDF-1.4\n% signed and notarized copy\n"],
            'jpeg' => ['IMG_2041.jpg', "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00"],
            'png' => ['scan.png', "\x89PNG\r\n\x1a\n\x00\x00\x00\x0DIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x02\x00\x00\x00\x90wS\xDE"],
            'heic' => ['IMG_2042.heic', "\x00\x00\x00\x18ftypheic\x00\x00\x00\x00mif1heic"],
        };

        return UploadedFile::fake()->createWithContent($name ?? $defaultName, $content);
    }
}

if (! function_exists('signedUploadRequest')) {
    /** Open a request on the filing as the admin, the way the status form does. */
    function signedUploadRequest(LienFiling $filing, User $admin, FilingStatus $status, string $message): LienFiling
    {
        test()->actingAs($admin);

        app(ChangeFilingStatus::class)->execute(
            filing: $filing,
            newStatus: $status,
            customerMessage: $message,
        );

        return $filing->fresh();
    }
}

if (! function_exists('signedUploadAsCustomer')) {
    function signedUploadAsCustomer(User $customer, Business $business): void
    {
        test()->actingAs($customer);
        session(['current_business_id' => $business->id]);
    }
}

describe('the upload box on the filing page', function () {
    beforeEach(function () {
        signedUploadAsCustomer($this->customer, $this->business);
    });

    it('shows with its checklist while the filing waits on the customer', function (FilingStatus $status) {
        // Moved there without a stored request: the box stands alone.
        $this->filing->update(['status' => $status]);

        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->assertSee('Upload your signed document')
            ->assertSee('Before you upload, check:')
            ->assertSee('A PDF is best. A clear photo works too.')
            ->assertSee('Every signature line is signed.')
            ->assertSee('If it was notarized, also check:')
            ->assertSee('The notary seal is easy to see.')
            ->assertSee("The notary's printed name and commission can be read.", false)
            ->assertSee('The county is filled in at the top of the notary section.')
            ->assertSee('PDF, JPG, PNG or HEIC, up to 20 MB')
            ->assertSee('Upload document')
            ->assertDontSee('What we need from you');
    })->with([
        'awaiting client' => [FilingStatus::AwaitingClient],
        'awaiting e-signature' => [FilingStatus::AwaitingEsign],
        'awaiting notary' => [FilingStatus::AwaitingNotary],
    ]);

    it('is not shown for any other status', function (FilingStatus $status) {
        $this->filing->update(['status' => $status]);

        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->assertDontSee('Upload your signed document');
    })->with([
        'paid' => [FilingStatus::Paid],
        'in fulfillment' => [FilingStatus::InFulfillment],
        'needs review' => [FilingStatus::NeedsReview],
        'ready to file' => [FilingStatus::ReadyToFile],
        'complete' => [FilingStatus::Complete],
    ]);

    it('sits in the request panel, before the answer form, for a notary request', function () {
        $filing = signedUploadRequest($this->filing, $this->admin, FilingStatus::AwaitingNotary, 'Please get this notarized and send it back.');
        signedUploadAsCustomer($this->customer, $this->business);

        $component = Livewire::test(FilingShow::class, ['filing' => $filing])
            ->assertSeeInOrder(['What we need from you', 'Please get this notarized and send it back.', 'Upload your signed document', 'Your answer']);

        expect(substr_count($component->html(), 'id="client-request"'))->toBe(1);
    });

    it('sits in the request panel, after the answer form, for an information request', function () {
        $filing = signedUploadRequest($this->filing, $this->admin, FilingStatus::AwaitingClient, 'What is the owner\'s mailing address?');
        signedUploadAsCustomer($this->customer, $this->business);

        $component = Livewire::test(FilingShow::class, ['filing' => $filing])
            ->assertSeeInOrder(['What we need from you', 'Your answer', 'Upload your signed document']);

        expect(substr_count($component->html(), 'id="client-request"'))->toBe(1);
    });
});

describe('uploading the signed document', function () {
    beforeEach(function () {
        $this->filing = signedUploadRequest($this->filing, $this->admin, FilingStatus::AwaitingNotary, 'Please get this notarized and send it back.');
        signedUploadAsCustomer($this->customer, $this->business);
    });

    it('stores it, logs it and moves the filing to review', function () {
        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf'))
            ->call('uploadSignedDocument')
            ->assertHasNoErrors()
            ->assertSee('We have your document')
            ->assertSee('We received notarized-lien.pdf.')
            ->assertSee("We'll check it and keep going. If the scan is hard to read we'll ask for a clearer one.", false)
            ->assertDontSee('Upload your signed document');

        $filing = $this->filing->fresh();
        $media = $filing->getFirstMedia('customer_uploads');

        expect($media)->not->toBeNull()
            ->and($media->file_name)->toBe('notarized-lien.pdf')
            ->and($media->disk)->toBe('s3')
            ->and($media->mime_type)->toBe('application/pdf')
            ->and($media->getCustomProperty('kind'))->toBe('signed_document');
        Storage::disk('s3')->assertExists($media->getPathRelativeToRoot());

        $upload = $filing->events()->where('event_type', 'document_uploaded')->sole();
        expect($upload->payload_json)->toEqual([
            'kind' => 'signed_document',
            'media_id' => $media->id,
            'original_name' => 'notarized-lien.pdf',
            'file_name' => 'notarized-lien.pdf',
            'size' => $media->size,
            'request_status' => 'awaiting_notary',
        ])
            ->and($upload->created_by)->toBe($this->customer->id)
            ->and($upload->description())->toBe('Customer uploaded a document');

        // Needs Review through transitionTo: the request closes, the reminders stop.
        expect($filing->status)->toBe(FilingStatus::NeedsReview)
            ->and($filing->client_request_json['resolved_at'])->not->toBeNull()
            ->and($filing->openClientRequest())->toBeNull();

        $statusEvent = $filing->events()->where('event_type', 'status_changed')->latest('id')->first();
        expect($statusEvent->payload_json['to'])->toBe('needs_review')
            ->and($statusEvent->meta('document_uploaded_id'))->toBe($upload->id);

        $sequence = EmailSequence::whereMorphedTo('sequenceable', $filing)
            ->where('sequence_type', 'filing_action_reminder')
            ->sole();
        expect($sequence->suppressed_at)->not->toBeNull()
            ->and($sequence->suppression_reason)->toBe('status_changed');

        // The lien board counts it as the customer's reply (EREG-133).
        expect($filing->customerRepliedAt()?->equalTo($upload->created_at))->toBeTrue();
    });

    it('tells lien ops, quoting the request', function () {
        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf'))
            ->call('uploadSignedDocument')
            ->assertHasNoErrors();

        Mail::assertQueued(ClientResponded::class, 1);
        $ops = Mail::queued(ClientResponded::class)->first();

        expect($ops->hasTo('liens@eregister.com'))->toBeTrue()
            ->and($ops->afterCommit)->toBeTrue()
            ->and($ops->uploadedDocument)->toBeTrue();
        $ops->assertHasSubject("Customer uploaded a document: Dana Drywall LLC — {$this->prelim->name}");
        $ops->assertSeeInHtml('Customer uploaded a document');
        $ops->assertSeeInHtml('Dana Builder uploaded their signed document');
        $ops->assertSeeInHtml('Maple Street Remodel');
        $ops->assertSeeInHtml('Please get this notarized and send it back.');
        $ops->assertSeeInHtml('notarized-lien.pdf');
        $ops->assertSeeInHtml(route('admin.liens.show', $this->filing));
        $ops->assertDontSeeInHtml('They answered:');
    });

    it('confirms to the customer by email, replying to support', function () {
        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf'))
            ->call('uploadSignedDocument')
            ->assertHasNoErrors();

        Mail::assertQueued(UploadReceived::class, 1);
        $mail = Mail::queued(UploadReceived::class)->first();

        expect($mail->hasTo('dana@example.test'))->toBeTrue()
            ->and($mail->afterCommit)->toBeTrue()
            ->and($mail->hasReplyTo('contact@eregister.com'))->toBeTrue();
        $mail->assertHasSubject('We have your document — eRegister');
        $mail->assertSeeInText('Hi Dana,');
        $mail->assertSeeInText("We have your document. We'll check it and keep going. If the scan is hard to read we'll ask for a clearer one.");
        $mail->assertSeeInText('notarized-lien.pdf');
        $mail->assertSeeInText($this->prelim->name);
        $mail->assertSeeInHtml(route('lien.filings.show', $this->filing));
    });

    it('takes PDF, JPG, PNG and HEIC files as they are', function (string $type, string $name, string $mime) {
        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile($type))
            ->call('uploadSignedDocument')
            ->assertHasNoErrors();

        $media = $this->filing->fresh()->getFirstMedia('customer_uploads');

        expect($media->file_name)->toBe($name)
            ->and($media->mime_type)->toBe($mime)
            ->and($this->filing->fresh()->status)->toBe(FilingStatus::NeedsReview);
    })->with([
        'pdf' => ['pdf', 'notarized-lien.pdf', 'application/pdf'],
        'jpg' => ['jpeg', 'IMG_2041.jpg', 'image/jpeg'],
        'png' => ['png', 'scan.png', 'image/png'],
        'heic' => ['heic', 'IMG_2042.heic', 'image/heic'],
    ]);

    it('needs a file', function () {
        $component = Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->call('uploadSignedDocument')
            ->assertHasErrors(['signedDocument' => 'required']);

        expect($component->errors()->first('signedDocument'))->toBe('Choose the file to upload.');
        expect($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingNotary);
    });

    it('rejects other file types', function () {
        $component = Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', UploadedFile::fake()->createWithContent('setup.exe', "MZ\x90\x00\x03\x00\x00\x00"))
            ->call('uploadSignedDocument')
            ->assertHasErrors(['signedDocument' => 'mimes']);

        expect($component->errors()->first('signedDocument'))->toBe('Upload a PDF, JPG, PNG or HEIC file.');
        expect($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingNotary);
    });

    it('checks what the file really is, not just its name', function (File $file) {
        $component = Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', $file)
            ->call('uploadSignedDocument')
            ->assertHasErrors('signedDocument');

        expect($component->errors()->first('signedDocument'))
            ->toBe('We could not open this file as a PDF, JPG, PNG or HEIC. Please upload the scan or photo itself.');

        $filing = $this->filing->fresh();
        expect($filing->status)->toBe(FilingStatus::AwaitingNotary)
            ->and($filing->getMedia('customer_uploads'))->toBeEmpty()
            ->and($filing->events()->where('event_type', 'document_uploaded')->exists())->toBeFalse();
        Mail::assertNotQueued(ClientResponded::class);
        Mail::assertNotQueued(UploadReceived::class);
    })->with([
        'a program named .pdf' => fn () => UploadedFile::fake()->createWithContent('signed.pdf', "MZ\x90\x00\x03\x00\x00\x00\x04\x00\x00\x00\xFF\xFF\x00\x00"),
        'text named .jpg' => fn () => UploadedFile::fake()->createWithContent('photo.jpg', 'This is not really a photo.'),
        'an empty .png' => fn () => UploadedFile::fake()->create('scan.png', 10, 'image/png'),
    ]);

    it('takes files up to 20 MB and no larger', function () {
        // Livewire's temporary upload limit matches (config/livewire.php).
        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf')->size(20481))
            ->assertHasErrors('signedDocument')
            ->call('uploadSignedDocument')
            ->assertHasErrors('signedDocument');

        expect($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingNotary);

        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf')->size(20480))
            ->assertHasNoErrors()
            ->call('uploadSignedDocument')
            ->assertHasNoErrors();

        expect($this->filing->fresh()->status)->toBe(FilingStatus::NeedsReview);
    });

    it('does not let someone outside the business upload', function () {
        $outsider = User::factory()->create();
        $otherBusiness = Business::factory()->onboarded()->withLienOnboarding()->create();
        $otherBusiness->users()->attach($outsider, ['role' => 'owner']);

        $component = Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf'));

        $this->actingAs($outsider);
        session(['current_business_id' => $otherBusiness->id]);

        $component->call('uploadSignedDocument')->assertForbidden();

        $filing = $this->filing->fresh();
        expect($filing->status)->toBe(FilingStatus::AwaitingNotary)
            ->and($filing->getMedia('customer_uploads'))->toBeEmpty();
        Mail::assertNotQueued(ClientResponded::class);
        Mail::assertNotQueued(UploadReceived::class);
    });

    it('stores nothing once the filing has moved on', function () {
        $component = Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf'));

        // Staff moved it on in the meantime.
        $this->filing->update(['status' => FilingStatus::ReadyToFile]);

        $component->call('uploadSignedDocument')
            ->assertHasNoErrors()
            ->assertSee('We are not waiting on a document for this filing anymore.');

        $filing = $this->filing->fresh();
        expect($filing->status)->toBe(FilingStatus::ReadyToFile)
            ->and($filing->getMedia('customer_uploads'))->toBeEmpty()
            ->and($filing->events()->where('event_type', 'document_uploaded')->exists())->toBeFalse();
        Mail::assertNotQueued(UploadReceived::class);
    });

    it('still confirms to the customer when the ops inbox is blank', function () {
        config(['lien.notifications.ops_email' => '']);

        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf'))
            ->call('uploadSignedDocument')
            ->assertHasNoErrors();

        expect($this->filing->fresh()->status)->toBe(FilingStatus::NeedsReview);
        Mail::assertNotQueued(ClientResponded::class);
        Mail::assertQueued(UploadReceived::class, 1);
    });
});

it('works without a stored request', function () {
    // Moved to Awaiting Notary before requests were stored.
    $this->filing->update(['status' => FilingStatus::AwaitingNotary]);
    signedUploadAsCustomer($this->customer, $this->business);

    Livewire::test(FilingShow::class, ['filing' => $this->filing])
        ->set('signedDocument', signedUploadFile('pdf'))
        ->call('uploadSignedDocument')
        ->assertHasNoErrors()
        ->assertSee('We received notarized-lien.pdf.');

    $filing = $this->filing->fresh();
    expect($filing->status)->toBe(FilingStatus::NeedsReview)
        ->and($filing->client_request_json)->toBeNull()
        ->and($filing->events()->where('event_type', 'document_uploaded')->sole()->payload_json['request_status'])->toBe('awaiting_notary');

    $ops = Mail::queued(ClientResponded::class)->sole();
    $ops->assertSeeInHtml('notarized-lien.pdf');
    $ops->assertDontSeeInHtml('We asked:');
    Mail::assertQueued(UploadReceived::class, 1);
});

it('works from Awaiting E-Signature and leaves the e-sign session alone', function () {
    $signatureRequest = SignatureRequest::create([
        'signable_type' => $this->filing->getMorphClass(),
        'signable_id' => $this->filing->id,
        'business_id' => $this->filing->business_id,
        'signer_user_id' => $this->customer->id,
        'document_signing_policy_key' => 'demand_letter',
        'status' => SignatureRequestStatus::AwaitingSignature,
        'signer_name_snapshot' => $this->customer->name,
        'signer_email_snapshot' => $this->customer->email,
        'invited_at' => now(),
        'expires_at' => now()->addDays(14),
    ]);
    $filing = signedUploadRequest($this->filing, $this->admin, FilingStatus::AwaitingEsign, 'Please sign the demand letter.');
    signedUploadAsCustomer($this->customer, $this->business);

    Livewire::test(FilingShow::class, ['filing' => $filing])
        ->assertSeeInOrder(['Review &amp; Sign', 'What we need from you', 'Upload your signed document'], false)
        ->set('signedDocument', signedUploadFile('pdf', 'wet-signed-letter.pdf'))
        ->call('uploadSignedDocument')
        ->assertHasNoErrors();

    expect($filing->fresh()->status)->toBe(FilingStatus::NeedsReview)
        ->and($signatureRequest->fresh()->status)->toBe(SignatureRequestStatus::AwaitingSignature);
});

describe('the admin filing page', function () {
    it('lists every customer upload with its size, time and download link', function () {
        $filing = signedUploadRequest($this->filing, $this->admin, FilingStatus::AwaitingClient, 'Please send the signed contract.');
        signedUploadAsCustomer($this->customer, $this->business);

        // An answer with a file (EREG-128), then the signed document.
        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->set('answer', 'Here is my last invoice.')
            ->set('answerFile', signedUploadFile('pdf', 'invoice-4411.pdf'))
            ->call('submitAnswer')
            ->assertHasNoErrors();

        $filing = signedUploadRequest($filing->fresh(), $this->admin, FilingStatus::AwaitingNotary, 'Please get this notarized.');
        signedUploadAsCustomer($this->customer, $this->business);

        Livewire::test(FilingShow::class, ['filing' => $filing])
            ->set('signedDocument', signedUploadFile('heic'))
            ->call('uploadSignedDocument')
            ->assertHasNoErrors();

        $uploads = $filing->fresh()->getMedia('customer_uploads');
        $invoice = $uploads->firstWhere('file_name', 'invoice-4411.pdf');
        $signed = $uploads->firstWhere('file_name', 'IMG_2042.heic');

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->assertSee('Requests and answers')
            ->assertSee('Customer uploads')
            ->assertSeeInOrder(['Customer uploads', 'IMG_2042.heic', 'invoice-4411.pdf'])
            ->assertSee($signed->human_readable_size)
            ->assertSee($signed->created_at->eastern()->format('M j, Y g:i A T'))
            ->assertSee('Signed document')
            ->assertSee(route('admin.liens.customer-uploads.download', [$filing->public_id, $signed->id]))
            ->assertSee(route('admin.liens.customer-uploads.download', [$filing->public_id, $invoice->id]))
            ->assertSee('Customer uploaded a document');
    });

    it('shows the card for a document uploaded without a request', function () {
        $this->filing->update(['status' => FilingStatus::AwaitingNotary]);
        signedUploadAsCustomer($this->customer, $this->business);

        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf'))
            ->call('uploadSignedDocument')
            ->assertHasNoErrors();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertSee('Requests and answers')
            ->assertSee('Customer uploads')
            ->assertSee('notarized-lien.pdf')
            ->assertDontSee('Open request');
    });
});

describe('the admin download', function () {
    beforeEach(function () {
        $this->filing->update(['status' => FilingStatus::AwaitingNotary]);
        signedUploadAsCustomer($this->customer, $this->business);

        Livewire::test(FilingShow::class, ['filing' => $this->filing])
            ->set('signedDocument', signedUploadFile('pdf'))
            ->call('uploadSignedDocument')
            ->assertHasNoErrors();

        $this->upload = $this->filing->fresh()->getFirstMedia('customer_uploads');
        $this->downloadUrl = route('admin.liens.customer-uploads.download', [$this->filing->public_id, $this->upload->id]);
    });

    it('sends staff to a short-lived link to the file', function () {
        $this->actingAs($this->admin);

        $response = $this->get($this->downloadUrl);

        $response->assertRedirect();
        expect($response->headers->get('Location'))
            ->toContain($this->upload->getPathRelativeToRoot())
            ->toContain('expiration=');
    });

    it('is forbidden to customers, even the filing\'s own business', function (string $who) {
        if ($who === 'another business') {
            $outsider = User::factory()->create();
            $otherBusiness = Business::factory()->onboarded()->withLienOnboarding()->create();
            $otherBusiness->users()->attach($outsider, ['role' => 'owner']);
            signedUploadAsCustomer($outsider, $otherBusiness);
        } else {
            signedUploadAsCustomer($this->customer, $this->business);
        }

        $this->get($this->downloadUrl)->assertForbidden();
    })->with([
        'a member of another business' => ['another business'],
        'a member of the filing\'s business' => ['own business'],
    ]);

    it('only serves that filing\'s customer uploads', function () {
        $this->actingAs($this->admin);

        $other = LienFiling::factory()->forProject($this->project)->paid()->create([
            'document_type_id' => $this->prelim->id,
            'created_by_user_id' => $this->customer->id,
        ]);
        $generated = $this->filing->addMedia(UploadedFile::fake()->createWithContent('draft.pdf', "%PDF-1.4\n"))
            ->toMediaCollection('generated');

        $this->get(route('admin.liens.customer-uploads.download', [$other->public_id, $this->upload->id]))->assertNotFound();
        $this->get(route('admin.liens.customer-uploads.download', [$this->filing->public_id, $generated->id]))->assertNotFound();
    });
});
