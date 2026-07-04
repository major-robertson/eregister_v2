<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Actions\CompleteSignature;
use App\Domains\Esign\Actions\VerifySignatureChain;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Livewire\SignConsent;
use App\Domains\Esign\Livewire\SignReview;
use App\Domains\Esign\Models\EsignConsent;
use App\Domains\Esign\Models\SignatureEvent;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Esign\Actions\SendDemandLetterForSignature;
use App\Domains\Lien\Livewire\FilingShow;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Mail\FilingActionReminder;
use App\Mail\SignerInvitation;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('s3');
    // Adopted signature PNGs (applied at signing) live on the resale-cert disk.
    Storage::fake(config('resale_cert.disk'));
});

function esignParty(LienProject $project, PartyRole $role, string $name, ?string $company = null, array $extra = []): LienParty
{
    return LienParty::create(array_merge([
        'business_id' => $project->business_id,
        'project_id' => $project->id,
        'role' => $role,
        'name' => $name,
        'company_name' => $company,
        'address1' => '1 Main St',
        'city' => 'Los Angeles',
        'state' => 'CA',
        'zip' => '90001',
    ], $extra));
}

/** A paid demand-letter filing with a verified signer (creator), a claimant, and 2 recipients. */
function esignDemandFiling(array $filingOverrides = [], array $signerAttributes = []): LienFiling
{
    $business = Business::factory()->create();
    $signer = User::factory()->create(array_merge(['email_verified_at' => now()], $signerAttributes));
    $signer->businesses()->attach($business->id, ['role' => 'owner']);

    $project = LienProject::factory()->forBusiness($business)->create([
        'first_furnish_date' => '2026-01-05',
        'last_furnish_date' => '2026-03-10',
    ]);

    esignParty($project, PartyRole::Claimant, 'Carl Claimant', 'Carl Construction LLC', [
        'email' => 'carl@example.com', 'phone' => '619-555-0100',
    ]);
    esignParty($project, PartyRole::Owner, 'Olivia Owner', 'Owner Holdings');
    esignParty($project, PartyRole::Customer, 'Casey Customer', 'Customer Co');

    $type = LienDocumentType::where('slug', 'demand_letter')->firstOrFail();

    return LienFiling::factory()->forProject($project)->paid()->create(array_merge([
        'document_type_id' => $type->id,
        'created_by_user_id' => $signer->id,
        'amount_claimed_cents' => 1_250_000,
        'description_of_work' => 'framing and drywall',
    ], $filingOverrides));
}

function esignAdmin(): User
{
    $admin = User::factory()->create();
    $admin->givePermissionTo(['lien.view', 'lien.change_status']);

    return $admin;
}

/** Send a filing for signature and return [filing, request, signer]. */
function esignSendFor(LienFiling $filing): SignatureRequest
{
    return app(SendDemandLetterForSignature::class)->execute($filing, esignAdmin());
}

/** Record consent and complete signing of an awaiting-signature request. */
function esignCompleteSign(SignatureRequest $request, User $signer): void
{
    $consent = EsignConsent::firstOrCreate(
        ['user_id' => $signer->id, 'consent_scope' => 'demand_letters', 'version' => config('esign.consent.version')],
        ['disclosure_text' => 'demo', 'hardware_software_ack' => true, 'consented_at' => now()],
    );
    $request->update(['consent_id' => $consent->id]);

    app(CompleteSignature::class)->execute(
        $request->fresh('documents'),
        $signer,
        'Carl Claimant',
        ['intent_statement' => config('esign.signing.intent'), 'document_list_snapshot' => []],
    );
}

describe('send for e-signature', function () {
    it('creates a session with locked + hashed documents, emits events, mails the signer, and moves to AwaitingEsign', function () {
        Mail::fake();
        $filing = esignDemandFiling();

        $request = esignSendFor($filing);

        expect($request->status)->toBe(SignatureRequestStatus::AwaitingSignature);
        expect($request->documents)->toHaveCount(2); // owner + customer (claimant excluded)

        foreach ($request->documents as $document) {
            expect($document->locked_document_hash)->not->toBeNull();
            expect($document->locked_at)->not->toBeNull();
            expect($document->lockedMedia())->not->toBeNull();
            expect($document->document_identifier)->toStartWith('DL-');
        }

        // Admin events, in order.
        $types = $request->events()->pluck('event_type')->map(fn ($t) => $t instanceof BackedEnum ? $t->value : $t)->all();
        expect($types)->toContain('document_created', 'document_locked', 'signer_invited');

        Mail::assertQueued(SignerInvitation::class, fn ($mail) => $mail->hasTo($filing->createdBy->email));

        $filing->refresh();
        expect($filing->status)->toBe(FilingStatus::AwaitingEsign);
        expect(EmailSequence::where('sequenceable_id', $filing->id)->where('trigger_status', 'awaiting_esign')->exists())->toBeTrue();
    });

    it('rejects a second active session', function () {
        Mail::fake();
        $filing = esignDemandFiling();
        esignSendFor($filing);

        expect(fn () => esignSendFor($filing->refresh()))
            ->toThrow(\App\Domains\Esign\Exceptions\EsignException::class);
    });

    it('rejects a non-demand-letter filing and a recipient-less filing', function () {
        Mail::fake();

        $lien = esignDemandFiling();
        $lien->update(['document_type_id' => LienDocumentType::where('slug', 'mechanics_lien')->firstOrFail()->id]);
        expect(fn () => esignSendFor($lien->refresh()))->toThrow(\App\Domains\Esign\Exceptions\EsignException::class);

        $business = Business::factory()->create();
        $signer = User::factory()->create(['email_verified_at' => now()]);
        $project = LienProject::factory()->forBusiness($business)->create();
        esignParty($project, PartyRole::Claimant, 'Solo Claimant', 'Solo LLC');
        $type = LienDocumentType::where('slug', 'demand_letter')->firstOrFail();
        $noRecipients = LienFiling::factory()->forProject($project)->paid()->create([
            'document_type_id' => $type->id, 'created_by_user_id' => $signer->id,
        ]);
        expect(fn () => esignSendFor($noRecipients))->toThrow(\App\Domains\Esign\Exceptions\EsignException::class);
    });
});

describe('hash-chained audit log', function () {
    it('produces a verifiable chain and detects tampering', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());

        $result = app(VerifySignatureChain::class)->execute($request);
        expect($result->valid)->toBeTrue();
        expect($result->eventCount)->toBeGreaterThan(0);

        // Tamper a row's metadata directly (bypassing the immutable model).
        $first = SignatureEvent::where('signature_request_id', $request->id)->orderBy('id')->first();
        DB::table('signature_events')->where('id', $first->id)->update(['metadata_json' => json_encode(['tampered' => true])]);

        $broken = app(VerifySignatureChain::class)->execute($request);
        expect($broken->valid)->toBeFalse();
        expect($broken->brokenAtEventId)->toBe($first->id);
    });

    it('forbids updating or deleting a signature event', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $event = $request->events()->first();

        expect(fn () => $event->update(['event_type' => 'document_viewed']))->toThrow(\RuntimeException::class);
        expect(fn () => $event->delete())->toThrow(\RuntimeException::class);
    });
});

describe('scoped consent', function () {
    it('records verbatim disclosures + scope/version, emits consent_accepted, and is reused', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $signer = $request->signer;

        $this->actingAs($signer);

        Livewire::test(SignConsent::class, ['request' => $request])
            ->set('acknowledged', true)
            ->call('accept')
            ->assertRedirect(route('esign.sign.review', $request->public_id));

        $consent = EsignConsent::where('user_id', $signer->id)->firstOrFail();
        expect($consent->consent_scope)->toBe('demand_letters');
        expect($consent->version)->toBe(config('esign.consent.version'));
        expect($consent->hardware_software_ack)->toBeTrue();
        expect($consent->disclosure_text)->toContain('withdraw');
        expect($consent->disclosure_snapshot_json)->toHaveKey('disclosures');
        expect($consent->consented_ip)->not->toBeNull();

        expect($request->events()->where('event_type', 'consent_accepted')->count())->toBe(1);

        // A different scope requires fresh consent.
        expect(EsignConsent::currentFor($signer, 'sales_tax_resale_certs', config('esign.consent.version')))->toBeNull();
        expect(EsignConsent::currentFor($signer, 'demand_letters', config('esign.consent.version')))->not->toBeNull();
    });

    it('requires the acknowledgement checkbox', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $this->actingAs($request->signer);

        Livewire::test(SignConsent::class, ['request' => $request])
            ->call('accept')
            ->assertHasErrors('acknowledged');

        expect(EsignConsent::count())->toBe(0);
    });
});

describe('signer flow', function () {
    it('opens, consents, reviews, and signs all letters', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $signer = $request->signer;
        $filing = $request->signable;

        // Landing (signed URL) records the open and routes to consent.
        $this->actingAs($signer)
            ->get(URL::temporarySignedRoute('esign.sign', now()->addDays(14), ['request' => $request->public_id]))
            ->assertRedirect(route('esign.sign.consent', $request->public_id));

        expect($request->fresh()->first_opened_at)->not->toBeNull();
        expect($request->events()->where('event_type', 'signer_opened')->count())->toBe(1);

        // Consent.
        Livewire::test(SignConsent::class, ['request' => $request])->set('acknowledged', true)->call('accept');

        // Review + sign all (typed signature captured in a script font,
        // which is also adopted as the signer's saved site-wide signature).
        Livewire::test(SignReview::class, ['request' => $request->fresh()])
            ->set('adoptedName', 'Carl Claimant')
            ->call('signAll', tinySignaturePng(), null, 'typed', 'dancing-script')
            ->assertRedirect(route('esign.sign.done', $request->public_id));

        $request->refresh()->load('documents');
        expect($request->status)->toBe(SignatureRequestStatus::Completed);
        expect($request->adopted_name)->toBe('Carl Claimant');
        expect($request->user_signature_id)->not->toBeNull();
        expect($request->signature_method)->toBe('typed_name');
        expect($signer->fresh()->currentSignature()->exists())->toBeTrue();
        expect($request->email_verified_at_sign)->not->toBeNull();
        expect($request->presented_text_json['document_list_snapshot'])->toHaveCount(2);

        foreach ($request->documents as $document) {
            expect($document->signed_document_hash)->not->toBeNull();
            expect($document->signedMedia())->not->toBeNull();
            expect($document->signed_document_hash)->not->toBe($document->locked_document_hash);
        }

        $eventTypes = $request->events()->pluck('event_type')->map(fn ($t) => $t instanceof BackedEnum ? $t->value : $t)->all();
        expect($eventTypes)->toContain('document_viewed', 'signature_started', 'signature_completed', 'final_pdf_generated');

        $filing->refresh();
        expect($filing->status)->toBe(FilingStatus::NeedsReview);
        expect(EmailSequence::where('sequenceable_id', $filing->id)->whereNull('suppressed_at')->where('trigger_status', 'awaiting_esign')->exists())->toBeFalse();
    });

    it('logs a signer download into the audit chain', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $signer = $request->signer;
        $this->actingAs($signer);

        Livewire::test(SignConsent::class, ['request' => $request])->set('acknowledged', true)->call('accept');
        Livewire::test(SignReview::class, ['request' => $request->fresh()])->set('adoptedName', 'Carl Claimant')->call('signAll', tinySignaturePng(), null, 'typed', 'dancing-script');

        Storage::disk('s3')->buildTemporaryUrlsUsing(fn ($path) => 'https://s3.test/'.$path);
        $document = $request->documents()->first();

        $this->get(route('esign.sign.download', ['request' => $request->public_id, 'document' => $document->public_id]))
            ->assertRedirect();

        expect(SignatureEvent::where('signature_request_id', $request->id)
            ->where('event_type', 'document_downloaded')->get()
            ->contains(fn ($e) => ($e->metadata_json['actor_role'] ?? null) === 'signer'))->toBeTrue();
    });
});

describe('authorization', function () {
    it('forbids a different account, redirects unverified, and blocks unsigned/voided/expired/trashed', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $signer = $request->signer;

        // Guest → login.
        $this->get(route('esign.sign.review', $request->public_id))->assertRedirect(route('login'));

        // Wrong account → 403.
        $this->actingAs(User::factory()->create(['email_verified_at' => now()]))
            ->get(route('esign.sign.review', $request->public_id))->assertForbidden();

        // Unverified signer → verification notice.
        $signer->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($signer)->get(route('esign.sign.review', $request->public_id))
            ->assertRedirect(route('verification.notice'));
        $signer->forceFill(['email_verified_at' => now()])->save();

        // Tampered/missing signature on the landing → 403.
        $this->actingAs($signer)->get(route('esign.sign', $request->public_id))->assertForbidden();

        // Voided → 410.
        $request->update(['status' => SignatureRequestStatus::Voided, 'voided_at' => now()]);
        $this->actingAs($signer)->get(route('esign.sign.review', $request->public_id))->assertStatus(410);
    });

    it('404s when the signable filing has been trashed', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $request->signable->delete();

        $this->actingAs($request->signer)
            ->get(route('esign.sign.review', $request->public_id))
            ->assertNotFound();
    });
});

describe('admin panel', function () {
    it('shows Send for E-Sign for a paid demand letter and verifies the chain', function () {
        Mail::fake();
        $filing = esignDemandFiling();
        $admin = esignAdmin();

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Send for E-Sign')
            ->call('sendForEsign')
            ->assertSee('E-Signature');

        $filing->refresh();
        expect($filing->status)->toBe(FilingStatus::AwaitingEsign);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->call('verifyChain')
            ->assertSee('Audit chain verified');
    });

    it('lets an admin download a signed document and logs the admin access', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $signer = $request->signer;
        $this->actingAs($signer);

        Livewire::test(SignConsent::class, ['request' => $request])->set('acknowledged', true)->call('accept');
        Livewire::test(SignReview::class, ['request' => $request->fresh()])->set('adoptedName', 'Carl Claimant')->call('signAll', tinySignaturePng(), null, 'typed', 'dancing-script');

        Storage::disk('s3')->buildTemporaryUrlsUsing(fn ($path) => 'https://s3.test/'.$path);
        $document = $request->documents()->first();

        $this->actingAs(esignAdmin())
            ->get(route('admin.liens.esign.documents.download', $document->public_id))
            ->assertRedirect();

        expect(SignatureEvent::where('signature_request_id', $request->id)
            ->where('event_type', 'document_downloaded')->get()
            ->contains(fn ($e) => ($e->metadata_json['actor_role'] ?? null) === 'admin'))->toBeTrue();
    });

    it('keeps signed documents downloadable after a re-send', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        esignCompleteSign($request, $request->signer);
        $filing = $request->signable->fresh();
        $signedDoc = $request->documents()->first();

        // Re-send starts a new awaiting session (filing back to AwaitingEsign).
        $newRequest = app(SendDemandLetterForSignature::class)->execute($filing, esignAdmin());
        expect($newRequest->status)->toBe(SignatureRequestStatus::AwaitingSignature);
        expect($filing->fresh()->status)->toBe(FilingStatus::AwaitingEsign);

        // The previously signed document is still admin-downloadable.
        Storage::disk('s3')->buildTemporaryUrlsUsing(fn ($p) => 'https://s3.test/'.$p);
        $this->actingAs(esignAdmin())
            ->get(route('admin.liens.esign.documents.download', $signedDoc->public_id))
            ->assertRedirect();

        // ...and the panel surfaces it even though the latest session is unsigned.
        $this->actingAs(esignAdmin());
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing->fresh()])
            ->assertSee('Signed documents')
            ->assertSeeHtml(route('admin.liens.esign.documents.download', $signedDoc->public_id));
    });

    it('warns in the confirmation when the filing was already e-signed', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        esignCompleteSign($request, $request->signer);
        $filing = $request->signable->fresh();

        $this->actingAs(esignAdmin());
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Re-send for E-Sign')
            ->call('confirmSendForEsign')
            ->assertSee('already been e-signed');
    });

    it('serves signed letters from the header Demand Letter download once signed', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        esignCompleteSign($request, $request->signer);
        $filing = $request->signable->fresh();
        $signedDoc = $request->documents()->first();

        $this->actingAs(esignAdmin());
        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            // Header dropdown links to the signed PDF and labels the on-the-fly letter as a draft.
            ->assertSeeHtml(route('admin.liens.esign.documents.download', $signedDoc->public_id))
            ->assertSee('unsigned draft');
    });
});

describe('policy + immutability', function () {
    it('rejects a document type that requires notarization', function () {
        Mail::fake();
        config(['esign.document_types.demand_letter.requires_notary' => true]);

        expect(fn () => esignSendFor(esignDemandFiling()))
            ->toThrow(\App\Domains\Esign\Exceptions\EsignException::class);
    });

    it('refuses to re-lock an already-locked document', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $document = $request->documents()->first();

        expect(fn () => $document->storeLocked('%PDF-fake', str_repeat('b', 64)))
            ->toThrow(\RuntimeException::class);
    });
});

describe('customer surfacing', function () {
    it('points the AwaitingEsign reminder CTA at the signing link', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $sequence = EmailSequence::where('sequenceable_id', $request->signable->id)
            ->where('trigger_status', 'awaiting_esign')->firstOrFail();

        $mail = new FilingActionReminder($sequence, 1);

        expect($mail->ctaUrl)->toContain('/esign/')->toContain($request->public_id);
    });

    it('shows a Review & Sign banner on the customer filing page', function () {
        Mail::fake();
        $request = esignSendFor(esignDemandFiling());
        $filing = $request->signable;

        $this->actingAs($request->signer);
        session(['current_business_id' => $filing->business_id]);

        Livewire::test(FilingShow::class, ['filing' => $filing->fresh()])
            ->assertSee('Review &amp; Sign', false);
    });
});
