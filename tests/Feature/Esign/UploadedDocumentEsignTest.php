<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Actions\VerifySignatureChain;
use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Livewire\SignConsent;
use App\Domains\Esign\Livewire\SignReview;
use App\Domains\Esign\Models\EsignConsent;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\SignableResolver;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Esign\Actions\SendUploadedDocumentForSignature;
use App\Domains\Lien\Esign\DemandLetterSignable;
use App\Domains\Lien\Esign\UploadedDocumentSignable;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Mail\SignerInvitation;
use App\Mail\SignerReminder;
use App\Models\EmailSequence;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/*
| EREG-141: a lien filing that is not a demand letter is e-signed in the app.
| The admin sends the newest uploaded draft; the customer signs it on the
| generic signing pages; the signed copy (the draft plus a signature and
| certificate page) is kept on the filing and linked on both sides.
*/

beforeEach(function () {
    Storage::fake('s3');
    // Adopted signature PNGs (applied at signing) live on the resale-cert disk.
    Storage::fake(config('resale_cert.disk'));
    Mail::fake();

    $this->customer = User::factory()->create([
        'first_name' => 'Dana',
        'last_name' => 'Builder',
        'email' => 'dana@example.test',
        'email_verified_at' => now(),
    ]);
    $this->business = Business::factory()->onboarded()->withLienOnboarding()->create(['name' => 'Dana Drywall LLC']);
    $this->business->users()->attach($this->customer, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Maple Street Remodel',
        'jobsite_state' => 'CA',
        'created_by_user_id' => $this->customer->id,
    ]);

    $this->filing = uploadedEsignFiling($this->project, $this->customer, 'prelim_notice', 'CA');

    $this->admin = User::factory()->create(['first_name' => 'Major', 'last_name' => 'Robertson']);
    $this->admin->givePermissionTo(['lien.view', 'lien.update', 'lien.change_status']);
});

if (! function_exists('uploadedEsignFiling')) {
    function uploadedEsignFiling(LienProject $project, User $creator, string $slug, ?string $state): LienFiling
    {
        return LienFiling::factory()->forProject($project)->paid()->create([
            'document_type_id' => LienDocumentType::where('slug', $slug)->firstOrFail()->id,
            'created_by_user_id' => $creator->id,
            'jurisdiction_state' => $state,
        ]);
    }
}

if (! function_exists('uploadedEsignPdf')) {
    /** A DOMPDF-made PDF, legal size, with the given number of pages. */
    function uploadedEsignPdf(int $pages = 2, string $marker = 'Draft'): string
    {
        $body = collect(range(1, $pages))
            ->map(fn (int $n) => '<div'.($n > 1 ? ' style="page-break-before: always"' : '').">{$marker} page {$n}</div>")
            ->implode('');

        $dompdf = new Dompdf;
        $dompdf->loadHtml("<html><body>{$body}</body></html>");
        $dompdf->setPaper('legal');
        $dompdf->render();

        return $dompdf->output();
    }
}

if (! function_exists('uploadedEsignDraft')) {
    /** Store a draft version on the filing, as the admin draft card does. */
    function uploadedEsignDraft(LienFiling $filing, User $admin, int $version, ?string $bytes = null): Media
    {
        return $filing->addMediaFromString($bytes ?? uploadedEsignPdf(2, "Version {$version}"))
            ->usingFileName("prelim-v{$version}.pdf")
            ->withCustomProperties(['version' => $version, 'uploaded_by_user_id' => $admin->id, 'source' => 'upload'])
            ->toMediaCollection('drafts');
    }
}

if (! function_exists('uploadedEsignXrefStreamPdf')) {
    /** A PDF 1.5 file whose cross-reference is a stream (FPDI's free parser can't read it). */
    function uploadedEsignXrefStreamPdf(): string
    {
        $pdf = "%PDF-1.5\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [] /Count 0 >>\nendobj\n";
        $xref = strlen($pdf);
        $pdf .= "3 0 obj\n<< /Type /XRef /Size 4 /Root 1 0 R /W [1 2 1] /Length 0 >>\nstream\n\nendstream\nendobj\n";

        return $pdf."startxref\n{$xref}\n%%EOF\n";
    }
}

if (! function_exists('uploadedEsignSend')) {
    function uploadedEsignSend(LienFiling $filing, User $admin): SignatureRequest
    {
        test()->actingAs($admin);

        return app(SendUploadedDocumentForSignature::class)->execute($filing->fresh(), $admin);
    }
}

if (! function_exists('uploadedEsignSign')) {
    /** The customer consents and signs on the generic pages. */
    function uploadedEsignSign(SignatureRequest $request, User $signer): void
    {
        test()->actingAs($signer);

        Livewire::test(SignConsent::class, ['request' => $request->fresh()])
            ->set('acknowledged', true)
            ->call('accept');

        Livewire::test(SignReview::class, ['request' => $request->fresh()])
            ->set('adoptedName', 'Dana Builder')
            ->call('signAll', tinySignaturePng(), null, 'typed', 'dancing-script')
            ->assertRedirect(route('esign.sign.done', $request->public_id));
    }
}

if (! function_exists('uploadedEsignPageSizes')) {
    /** @return list<array{0: float, 1: float}> */
    function uploadedEsignPageSizes(string $bytes): array
    {
        $pdf = new Fpdi;
        $count = $pdf->setSourceFile(StreamReader::createByString($bytes));

        return collect(range(1, $count))->map(function (int $n) use ($pdf) {
            $size = $pdf->getTemplateSize($pdf->importPage($n));

            return [round($size['width'], 1), round($size['height'], 1)];
        })->all();
    }
}

if (! function_exists('uploadedEsignBytes')) {
    function uploadedEsignBytes(Media $media): string
    {
        return Storage::disk($media->disk)->get($media->getPathRelativeToRoot());
    }
}

describe('resolver and policy', function () {
    it('resolves a demand letter to its letters and every other filing to its uploaded draft', function () {
        $resolver = app(SignableResolver::class);

        expect($resolver->for($this->filing))->toBeInstanceOf(UploadedDocumentSignable::class);

        foreach (['noi', 'mechanics_lien', 'lien_release'] as $slug) {
            expect($resolver->for(uploadedEsignFiling($this->project, $this->customer, $slug, 'CA')))
                ->toBeInstanceOf(UploadedDocumentSignable::class);
        }

        expect($resolver->for(uploadedEsignFiling($this->project, $this->customer, 'demand_letter', 'CA')))
            ->toBeInstanceOf(DemandLetterSignable::class);
    });

    it('allows notices in every state and liens and releases only in the listed states', function () {
        $filing = fn (string $slug, ?string $state) => uploadedEsignFiling($this->project, $this->customer, $slug, $state);

        expect(UploadedDocumentSignable::isAllowedFor($filing('prelim_notice', 'TX')))->toBeTrue()
            ->and(UploadedDocumentSignable::isAllowedFor($filing('noi', 'NY')))->toBeTrue()
            ->and(UploadedDocumentSignable::isAllowedFor($filing('mechanics_lien', 'TX')))->toBeFalse()
            ->and(UploadedDocumentSignable::isAllowedFor($filing('lien_release', 'CA')))->toBeFalse()
            ->and(UploadedDocumentSignable::isAllowedFor($filing('demand_letter', 'CA')))->toBeFalse();

        expect(UploadedDocumentSignable::notAllowedReason($filing('mechanics_lien', 'TX')))
            ->toBe('E-sign is not enabled for Mechanics Lien in Texas. Use the notarization path.')
            ->and(UploadedDocumentSignable::notAllowedReason($filing('prelim_notice', 'TX')))->toBeNull()
            ->and(UploadedDocumentSignable::notAllowedReason($filing('demand_letter', 'TX')))->toBeNull();

        config(['esign.uploaded_documents.lien_states' => ['tx', 'FL']]);

        expect(UploadedDocumentSignable::isAllowedFor($filing('mechanics_lien', 'TX')))->toBeTrue()
            ->and(UploadedDocumentSignable::isAllowedFor($filing('lien_release', 'FL')))->toBeTrue()
            ->and(UploadedDocumentSignable::isAllowedFor($filing('lien_release', 'CA')))->toBeFalse()
            // No filing state: the project's jobsite state (CA) decides.
            ->and(UploadedDocumentSignable::isAllowedFor($filing('mechanics_lien', null)))->toBeFalse();
    });

    it('reads the signing strings per document type and falls back to the global ones', function () {
        $uploaded = DocumentSigningPolicy::for('uploaded_document');

        expect($uploaded->title())->toBe('Lien document')
            ->and($uploaded->signButton())->toBe('Sign document')
            ->and($uploaded->intentStatement())->toBe('I have reviewed the document shown below. By clicking Sign document, I intend to electronically sign it.')
            ->and($uploaded->documentIdPrefix())->toBe('LD')
            ->and($uploaded->consentScope())->toBe('lien_documents')
            ->and($uploaded->allowedSignerRole())->toBe('filing_creator')
            ->and($uploaded->documentNoun())->toBe('document')
            ->and($uploaded->showsInlinePreview())->toBeTrue();

        $demand = DocumentSigningPolicy::for('demand_letter');

        expect($demand->signButton())->toBe(config('esign.signing.sign_button'))
            ->and($demand->intentStatement())->toBe(config('esign.signing.intent'))
            ->and($demand->documentNoun())->toBe('letter')
            ->and($demand->showsInlinePreview())->toBeFalse();
    });
});

describe('sending for e-signature', function () {
    it('locks the newest draft as uploaded, freezes its hash, emails the signer and waits on the signature', function () {
        uploadedEsignDraft($this->filing, $this->admin, 1);
        $newest = uploadedEsignDraft($this->filing, $this->admin, 2);
        $bytes = uploadedEsignBytes($newest);

        $request = uploadedEsignSend($this->filing, $this->admin);

        expect($request->status)->toBe(SignatureRequestStatus::AwaitingSignature)
            ->and($request->document_signing_policy_key)->toBe('uploaded_document')
            ->and($request->signer_user_id)->toBe($this->customer->id)
            ->and($request->intent_statement)->toBe(config('esign.document_types.uploaded_document.intent'))
            ->and($request->documents)->toHaveCount(1);

        $document = $request->documents->first();
        $snapshot = $document->document_snapshot_json;

        expect($document->document_identifier)->toBe('LD-1001')
            ->and($document->label)->toBe('Preliminary Notice v2')
            // MySQL's JSON column does not keep key order.
            ->and($snapshot['render'])->toEqual([
                'media_id' => $newest->id,
                'version' => 2,
                'file_name' => 'prelim-v2.pdf',
                'sha256' => hash('sha256', $bytes),
            ])
            ->and($snapshot['meta'])->toMatchArray([
                'document_type' => 'uploaded_document',
                'lien_document_type' => 'prelim_notice',
                'media_id' => $newest->id,
                'version' => 2,
                'sha256' => hash('sha256', $bytes),
                'stamper_version' => 1,
                'unsigned_pdf_hash' => hash('sha256', $bytes),
            ])
            ->and($document->locked_document_hash)->toBe(hash('sha256', $bytes))
            ->and(uploadedEsignBytes($document->lockedMedia()))->toBe($bytes);

        $filing = $this->filing->fresh();
        expect($filing->status)->toBe(FilingStatus::AwaitingEsign)
            ->and(EmailSequence::where('sequenceable_id', $filing->id)->where('trigger_status', 'awaiting_esign')->whereNull('suppressed_at')->exists())->toBeTrue();

        $sent = $filing->events()->where('event_type', 'esign_sent')->sole();
        expect($sent->payload_json)->toMatchArray([
            'signature_request_id' => $request->public_id,
            'signer_email' => 'dana@example.test',
            'media_id' => $newest->id,
            'version' => 2,
            'file_name' => 'prelim-v2.pdf',
        ]);

        $mail = Mail::queued(SignerInvitation::class)->sole();
        expect($mail->hasTo('dana@example.test'))->toBeTrue();
        $mail->assertSeeInHtml('Your Lien document is ready for your electronic signature.');
        $mail->assertSeeInHtml('the document, and adopt your signature');
    });

    it('refuses without a draft, where e-sign is off, for a PDF it cannot sign and for a demand letter', function () {
        expect(fn () => uploadedEsignSend($this->filing, $this->admin))
            ->toThrow(EsignException::class, 'Upload the document as a draft first');

        $lien = uploadedEsignFiling($this->project, $this->customer, 'mechanics_lien', 'TX');
        uploadedEsignDraft($lien, $this->admin, 1);
        expect(fn () => uploadedEsignSend($lien, $this->admin))
            ->toThrow(EsignException::class, 'E-sign is not enabled for Mechanics Lien in Texas. Use the notarization path.');

        uploadedEsignDraft($this->filing, $this->admin, 1, uploadedEsignXrefStreamPdf());
        expect(fn () => uploadedEsignSend($this->filing, $this->admin))
            ->toThrow(EsignException::class, "This PDF uses a format we can't sign");

        $demand = uploadedEsignFiling($this->project, $this->customer, 'demand_letter', 'CA');
        expect(fn () => uploadedEsignSend($demand, $this->admin))
            ->toThrow(EsignException::class, 'demand letter');

        // Nothing was stored or moved.
        expect(SignatureRequest::count())->toBe(0)
            ->and($this->filing->fresh()->status)->toBe(FilingStatus::Paid)
            ->and($lien->fresh()->status)->toBe(FilingStatus::Paid);
        Mail::assertNotQueued(SignerInvitation::class);
    });

    it('rejects a second active request', function () {
        uploadedEsignDraft($this->filing, $this->admin, 1);
        uploadedEsignSend($this->filing, $this->admin);

        expect(fn () => uploadedEsignSend($this->filing, $this->admin))
            ->toThrow(EsignException::class, 'already has an active signature request');
    });
});

describe('signing', function () {
    it('signs on the generic pages, keeps the signed copy on the filing and moves it to Needs Review', function () {
        $draft = uploadedEsignDraft($this->filing, $this->admin, 1);
        $source = uploadedEsignBytes($draft);
        $request = uploadedEsignSend($this->filing, $this->admin);
        $document = $request->documents->first();

        $this->actingAs($this->customer)
            ->get(URL::temporarySignedRoute('esign.sign', now()->addDays(14), ['request' => $request->public_id]))
            ->assertRedirect(route('esign.sign.consent', $request->public_id));

        Livewire::test(SignConsent::class, ['request' => $request->fresh()])
            ->set('acknowledged', true)
            ->call('accept')
            ->assertRedirect(route('esign.sign.review', $request->public_id));

        expect(EsignConsent::where('user_id', $this->customer->id)->sole()->consent_scope)->toBe('lien_documents');

        Livewire::test(SignReview::class, ['request' => $request->fresh()])
            ->assertSee('Review &amp; sign your Lien document', false)
            ->assertSee('I have reviewed the document shown below.')
            ->assertSee('Sign document')
            ->assertSeeHtml('src="'.route('esign.sign.preview', ['request' => $request->public_id, 'document' => $document->public_id]).'"')
            ->set('adoptedName', 'Dana Builder')
            ->call('signAll', tinySignaturePng(), null, 'typed', 'dancing-script')
            ->assertRedirect(route('esign.sign.done', $request->public_id));

        $request->refresh();
        $document->refresh();

        expect($request->status)->toBe(SignatureRequestStatus::Completed)
            ->and($request->presented_text_json['sign_button'])->toBe('Sign document')
            ->and($request->presented_text_json['intent_statement'])->toBe(config('esign.document_types.uploaded_document.intent'))
            ->and($document->signed_at)->not->toBeNull()
            ->and(app(VerifySignatureChain::class)->execute($request)->valid)->toBeTrue();

        // The signed copy: the draft's pages untouched, then one signature page.
        $signedBytes = uploadedEsignBytes($document->signedMedia());
        $sizes = uploadedEsignPageSizes($signedBytes);
        expect($sizes)->toHaveCount(3)
            ->and(array_slice($sizes, 0, 2))->toBe(uploadedEsignPageSizes($source))
            ->and($document->signed_document_hash)->toBe(hash('sha256', $signedBytes));

        // ...kept on the filing in the signed collection.
        $filing = $this->filing->fresh();
        $signed = $filing->getMedia('signed')->sole();
        expect($signed->file_name)->toBe('prelim-v1-signed.pdf')
            ->and($signed->getCustomProperty('signature_request_id'))->toBe($request->public_id)
            ->and($signed->getCustomProperty('version'))->toBe(1)
            ->and($signed->getCustomProperty('draft_media_id'))->toBe($draft->id)
            ->and(uploadedEsignBytes($signed))->toBe($signedBytes);

        expect($filing->status)->toBe(FilingStatus::NeedsReview)
            ->and(EmailSequence::where('sequenceable_id', $filing->id)->where('trigger_status', 'awaiting_esign')->whereNull('suppressed_at')->exists())->toBeFalse();

        $completed = $filing->events()->where('event_type', 'esign_completed')->sole();
        expect($completed->payload_json)->toMatchArray([
            'signature_request_id' => $request->public_id,
            'media_id' => $signed->id,
            'version' => 1,
            'file_name' => 'prelim-v1-signed.pdf',
        ]);
    });

    it('shows the locked PDF inline to the signer only', function () {
        $draft = uploadedEsignDraft($this->filing, $this->admin, 1);
        $request = uploadedEsignSend($this->filing, $this->admin);
        $document = $request->documents->first();
        $url = route('esign.sign.preview', ['request' => $request->public_id, 'document' => $document->public_id]);

        $response = $this->actingAs($this->customer)->get($url);
        $response->assertOk();
        expect($response->headers->get('content-type'))->toContain('application/pdf')
            ->and($response->streamedContent())->toBe(uploadedEsignBytes($draft));

        $this->actingAs(User::factory()->create(['email_verified_at' => now()]))->get($url)->assertForbidden();

        $request->update(['status' => SignatureRequestStatus::Voided, 'voided_at' => now()]);
        $this->actingAs($this->customer)->get($url)->assertStatus(410);
    });
});

describe('admin page', function () {
    it('offers Send for E-Sign with the draft that will be signed, without a party picker, and sends it', function () {
        $this->travelTo(now()->startOfMinute());
        uploadedEsignDraft($this->filing, $this->admin, 1);
        uploadedEsignDraft($this->filing, $this->admin, 2);
        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertSee('Send for E-Sign')
            ->call('confirmSendForEsign')
            ->assertSet('showSendEsignModal', true)
            ->assertSee('prelim-v2.pdf')
            ->assertSee('Uploaded '.now()->eastern()->format('M j, Y g:i A T').' by Major Robertson')
            ->assertSee('exactly as uploaded')
            ->assertDontSee('Send a letter to')
            ->call('sendForEsign')
            ->assertHasNoErrors()
            ->assertSee('Sent for e-signature.')
            ->assertSee('draft v2 (prelim-v2.pdf)');

        $request = $this->filing->fresh()->signatureRequests()->sole();
        expect($request->documents()->sole()->label)->toBe('Preliminary Notice v2')
            ->and($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingEsign);
    });

    it('hides Send for E-Sign without a draft, and says why where e-sign is not enabled', function () {
        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertDontSeeHtml('wire:click="confirmSendForEsign"')
            ->assertDontSee('E-sign is not enabled');

        $lien = uploadedEsignFiling($this->project, $this->customer, 'mechanics_lien', 'TX');
        uploadedEsignDraft($lien, $this->admin, 1);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $lien->fresh()])
            ->assertDontSeeHtml('wire:click="confirmSendForEsign"')
            ->assertSee('E-sign is not enabled for Mechanics Lien in Texas. Use the notarization path.');

        config(['esign.uploaded_documents.lien_states' => ['TX']]);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $lien->fresh()])
            ->assertSeeHtml('wire:click="confirmSendForEsign"')
            ->assertDontSee('E-sign is not enabled');
    });

    it('shows the admin why a PDF cannot be signed', function () {
        uploadedEsignDraft($this->filing, $this->admin, 1, uploadedEsignXrefStreamPdf());
        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->call('confirmSendForEsign')
            ->call('sendForEsign')
            ->assertSee("This PDF uses a format we can't sign. Re-save it as PDF 1.4 or print it to PDF, then upload it again.");

        expect($this->filing->fresh()->signatureRequests()->exists())->toBeFalse();
    });

    it('sends a reminder and voids an uploaded document request', function () {
        uploadedEsignDraft($this->filing, $this->admin, 1);
        $request = uploadedEsignSend($this->filing, $this->admin);
        $this->travel(1)->hours();
        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertSee('Signing link')
            ->call('sendSignerReminder')
            ->assertSee('Reminder emailed to dana@example.test');

        $reminder = Mail::queued(SignerReminder::class)->sole();
        expect($reminder->hasTo('dana@example.test'))->toBeTrue();
        $reminder->assertSeeInHtml('the document, and adopt your signature');
        expect($request->events()->where('event_type', 'reminder_sent')->exists())->toBeTrue();

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->call('voidEsign')
            ->assertSee('Signature request voided.');

        expect($request->fresh()->status)->toBe(SignatureRequestStatus::Voided)
            ->and($this->filing->fresh()->status)->toBe(FilingStatus::NeedsReview);
    });

    it('links the signed PDF in the e-signature panel once signed', function () {
        uploadedEsignDraft($this->filing, $this->admin, 1);
        $request = uploadedEsignSend($this->filing, $this->admin);
        uploadedEsignSign($request, $this->customer);
        $document = $request->documents()->sole();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $this->filing->fresh()])
            ->assertSee('Signed PDF: Preliminary Notice v1')
            ->assertSeeHtml(route('admin.liens.esign.documents.download', $document->public_id))
            ->assertSee('E-signature completed');

        $this->get(route('admin.liens.esign.documents.download', $document->public_id))->assertRedirect();
    });
});

describe('customer filing page', function () {
    it('shows the signature on the timeline with a link to the signed copy', function () {
        uploadedEsignDraft($this->filing, $this->admin, 1);
        $request = uploadedEsignSend($this->filing, $this->admin);

        $this->actingAs($this->customer);
        session(['current_business_id' => $this->business->id]);

        Livewire::test(FilingShow::class, ['filing' => $this->filing->fresh()])
            ->assertSee('Review &amp; Sign', false)
            ->assertDontSee('You signed the document');

        uploadedEsignSign($request, $this->customer);

        $this->actingAs($this->customer);
        session(['current_business_id' => $this->business->id]);

        Livewire::test(FilingShow::class, ['filing' => $this->filing->fresh()])
            ->assertSee('You signed the document (version 1)')
            ->assertSee('Signed copy (prelim-v1-signed.pdf)')
            ->assertSeeInOrder(['You signed the document (version 1)', 'Needs Review']);
    });
});
