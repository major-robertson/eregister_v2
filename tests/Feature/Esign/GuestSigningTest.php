<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Actions\SendGuestSignerCode;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Livewire\SignConsent;
use App\Domains\Esign\Livewire\SignReview;
use App\Domains\Esign\Livewire\SignVerifyIdentity;
use App\Domains\Esign\Models\EsignConsent;
use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\Support\GuestSignerSession;
use App\Domains\Lien\Documents\WaiverGenerator;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Esign\Actions\SendWaiverForSignature;
use App\Domains\Lien\Esign\LienWaiverSignable;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Mail\GuestSignerCode;
use App\Mail\WaiverCompleted;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('s3');
    Mail::fake();
});

/**
 * A collect-direction waiver (vendor signs as a guest) that has been generated
 * with a frozen render snapshot, ready for SendWaiverForSignature.
 */
function guestSigningWaiver(array $overrides = []): LienWaiver
{
    $business = Business::factory()->create();
    $creator = User::factory()->create(['email_verified_at' => now()]);
    $project = LienProject::factory()->forBusiness($business)->inState('CO')->create();

    $waiver = LienWaiver::factory()->forProject($project)->collect()->generated()->create(array_merge([
        'created_by_user_id' => $creator->id,
        'signer_name' => 'Vera Vendor',
        'signer_email' => 'vendor@example.com',
    ], $overrides));

    $waiver->update(['render_snapshot_json' => app(WaiverGenerator::class)->data($waiver)]);

    return $waiver->fresh();
}

/**
 * Fabricate a guest signing session directly (no PDF rendering), enough for
 * the middleware / verify-screen behaviors that never touch documents.
 */
function guestSigningRequest(array $overrides = [], ?LienWaiver $waiver = null): SignatureRequest
{
    $waiver ??= guestSigningWaiver();

    return SignatureRequest::create(array_merge([
        'signable_type' => 'lien_waiver',
        'signable_id' => $waiver->id,
        'business_id' => $waiver->business_id,
        'signer_user_id' => null,
        'document_signing_policy_key' => LienWaiverSignable::DOCUMENT_TYPE,
        'status' => SignatureRequestStatus::AwaitingSignature,
        'signer_name_snapshot' => $waiver->signer_name,
        'signer_email_snapshot' => $waiver->signer_email,
        'invited_at' => now(),
        'expires_at' => now()->addDays(14),
    ], $overrides));
}

/** Arm a known one-time code so the test can type it back deterministically. */
function guestSigningArmCode(SignatureRequest $request, string $code = '123456'): void
{
    $request->update([
        'guest_code_hash' => hash('sha256', $code),
        'guest_code_expires_at' => now()->addMinutes(10),
        'guest_code_attempts' => 0,
    ]);
}

describe('guest entry funnels to email verification', function () {
    it('redirects a guest landing on the signed URL to the verify screen', function () {
        $request = guestSigningRequest();

        $this->get(URL::temporarySignedRoute('esign.sign', now()->addDays(14), ['request' => $request->public_id]))
            ->assertRedirect(route('esign.sign.verify', $request->public_id));

        // For guests the "opened" moment is recorded at identity verification.
        expect($request->fresh()->first_opened_at)->toBeNull();
    });

    it('redirects unverified guests to verify from consent, review, and done', function () {
        $request = guestSigningRequest();
        $verify = route('esign.sign.verify', $request->public_id);

        $this->get(route('esign.sign.consent', $request->public_id))->assertRedirect($verify);
        $this->get(route('esign.sign.review', $request->public_id))->assertRedirect($verify);
        $this->get(route('esign.sign.done', $request->public_id))->assertRedirect($verify);
    });

    it('issues a code automatically on first arrival at the verify screen', function () {
        $request = guestSigningRequest();
        session()->put(GuestSignerSession::challengedKey($request), now()->toIso8601String());

        Livewire::test(SignVerifyIdentity::class, ['request' => $request])
            ->assertSee('ve••••@example.com');

        $request->refresh();
        expect($request->guest_code_hash)->not->toBeNull();
        expect($request->guest_code_expires_at)->not->toBeNull();

        Mail::assertQueued(GuestSignerCode::class, fn (GuestSignerCode $mail) => $mail->hasTo('vendor@example.com'));
    });

    it('never emails a code to a session that did not arrive via the signed link', function () {
        $request = guestSigningRequest();

        // Mount without the landing marker: no auto-send, and the page asks
        // the visitor to use their emailed link instead.
        $component = Livewire::test(SignVerifyIdentity::class, ['request' => $request])
            ->assertSee('open the signing link');

        expect($request->fresh()->guest_code_hash)->toBeNull();

        // Resend is refused too; knowing the URL alone can't trigger emails.
        $component->call('resend')->assertHasErrors('code');

        Mail::assertNotQueued(GuestSignerCode::class);
    });

    it('caps lifetime code sends per request', function () {
        $request = guestSigningRequest();

        foreach (range(1, SendGuestSignerCode::MAX_SENDS) as $i) {
            expect(app(SendGuestSignerCode::class)->execute($request->fresh()))->toBeTrue();
            $this->travel(61)->seconds();
        }

        expect(app(SendGuestSignerCode::class)->execute($request->fresh()))->toBeFalse();
        Mail::assertQueued(GuestSignerCode::class, SendGuestSignerCode::MAX_SENDS);
    });
});

describe('SendGuestSignerCode', function () {
    it('stores only the sha256 hash + expiry, mails the code, and throttles resends to one per minute', function () {
        $request = guestSigningRequest();

        app(SendGuestSignerCode::class)->execute($request);
        $request->refresh();

        $code = null;
        Mail::assertQueued(GuestSignerCode::class, function (GuestSignerCode $mail) use (&$code, $request) {
            $code = $mail->code;

            return $mail->hasTo($request->signer_email_snapshot);
        });

        expect($code)->toMatch('/^\d{6}$/');
        expect($request->guest_code_hash)->toBe(hash('sha256', $code));
        expect($request->guest_code_hash)->not->toBe($code);
        expect($request->guest_code_expires_at->timestamp)
            ->toBeGreaterThan(now()->addMinutes(SendGuestSignerCode::TTL_MINUTES - 1)->timestamp);
        expect($request->guest_code_last_sent_at)->not->toBeNull();
        expect($request->guest_code_attempts)->toBe(0);

        // An immediate re-send is quietly ignored: no new mail, same hash.
        app(SendGuestSignerCode::class)->execute($request->fresh());
        Mail::assertQueued(GuestSignerCode::class, 1);
        expect($request->fresh()->guest_code_hash)->toBe(hash('sha256', $code));

        // Past the 60s window a fresh code goes out and attempts reset.
        $request->update(['guest_code_attempts' => 3]);
        $this->travel(61)->seconds();
        app(SendGuestSignerCode::class)->execute($request->fresh());

        Mail::assertQueued(GuestSignerCode::class, 2);
        expect($request->fresh()->guest_code_attempts)->toBe(0);
    });

    it('refuses account sessions and sessions with no signer email', function () {
        $signer = User::factory()->create(['email_verified_at' => now()]);

        $account = guestSigningRequest(['signer_user_id' => $signer->id]);
        expect(fn () => app(SendGuestSignerCode::class)->execute($account))
            ->toThrow(EsignException::class, 'belongs to an account');

        $noEmail = guestSigningRequest(['signer_email_snapshot' => null]);
        expect(fn () => app(SendGuestSignerCode::class)->execute($noEmail))
            ->toThrow(EsignException::class, 'no signer email');

        Mail::assertNotQueued(GuestSignerCode::class);
    });
});

describe('SignVerifyIdentity', function () {
    it('rejects a wrong code and counts the attempt', function () {
        $request = guestSigningRequest();
        guestSigningArmCode($request);

        $component = Livewire::test(SignVerifyIdentity::class, ['request' => $request->fresh()]);

        $component->set('code', '000000')->call('verify')
            ->assertHasErrors('code')
            ->assertSee('Check your email and try again');
        expect($request->fresh()->guest_code_attempts)->toBe(1);

        $component->set('code', '999999')->call('verify')->assertHasErrors('code');
        expect($request->fresh()->guest_code_attempts)->toBe(2);

        expect($request->fresh()->guest_verified_at)->toBeNull();
        expect(GuestSignerSession::isVerified($request))->toBeFalse();
    });

    it('locks out after 5 attempts and recovers via a fresh code', function () {
        $request = guestSigningRequest();
        guestSigningArmCode($request);
        session()->put(GuestSignerSession::challengedKey($request), now()->toIso8601String());

        $component = Livewire::test(SignVerifyIdentity::class, ['request' => $request->fresh()]);

        foreach (range(1, 5) as $attempt) {
            $component->set('code', '000000')->call('verify')->assertHasErrors('code');
            expect($request->fresh()->guest_code_attempts)->toBe($attempt);
        }

        // Even the correct code is refused once the counter hits the max.
        $component->set('code', '123456')->call('verify')
            ->assertHasErrors('code')
            ->assertSee('Too many attempts');

        expect($request->fresh()->guest_verified_at)->toBeNull();
        expect(GuestSignerSession::isVerified($request))->toBeFalse();

        // Re-sending resets the counter; the newly mailed code verifies.
        $component->call('resend');
        expect($request->fresh()->guest_code_attempts)->toBe(0);

        $code = null;
        Mail::assertQueued(GuestSignerCode::class, function (GuestSignerCode $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $component->set('code', $code)->call('verify')->assertHasNoErrors();
        expect($request->fresh()->guest_verified_at)->not->toBeNull();
    });

    it('rejects an expired code', function () {
        $request = guestSigningRequest();
        guestSigningArmCode($request);

        $component = Livewire::test(SignVerifyIdentity::class, ['request' => $request->fresh()]);

        // Expire the code after mount (mount would auto-replace an already-expired one).
        $request->update(['guest_code_expires_at' => now()->subMinute()]);

        $component->set('code', '123456')->call('verify')
            ->assertHasErrors('code')
            ->assertSee('That code has expired');

        expect($request->fresh()->guest_verified_at)->toBeNull();
    });

    it('verifies the correct code: marks the session, appends events, and redirects to consent', function () {
        $request = guestSigningRequest();
        guestSigningArmCode($request);

        Livewire::test(SignVerifyIdentity::class, ['request' => $request->fresh()])
            ->set('code', '123456')
            ->call('verify')
            ->assertHasNoErrors()
            ->assertRedirect(route('esign.sign.consent', $request->public_id));

        $request->refresh();
        expect($request->guest_verified_at)->not->toBeNull();
        expect($request->first_opened_at)->not->toBeNull();
        // The used code is cleared so it can't be replayed elsewhere.
        expect($request->guest_code_hash)->toBeNull();
        expect($request->guest_code_expires_at)->toBeNull();

        expect(session()->has(GuestSignerSession::verifiedKey($request)))->toBeTrue();

        $types = $request->events()->pluck('event_type')
            ->map(fn ($t) => $t instanceof BackedEnum ? $t->value : $t)->all();
        expect($types)->toContain('signer_identity_verified', 'signer_opened');
    });
});

describe('full guest ceremony', function () {
    it('verifies, consents, and signs with a typed name, storing the signed PDF', function () {
        $waiver = guestSigningWaiver();
        $creator = $waiver->createdBy;

        $request = app(SendWaiverForSignature::class)->execute($waiver, $creator);
        expect($request->signer_user_id)->toBeNull();

        // 1. Signed URL landing → verify.
        $this->get(URL::temporarySignedRoute('esign.sign', now()->addDays(14), ['request' => $request->public_id]))
            ->assertRedirect(route('esign.sign.verify', $request->public_id));

        // 2. One-time email code.
        guestSigningArmCode($request);
        Livewire::test(SignVerifyIdentity::class, ['request' => $request->fresh()])
            ->set('code', '123456')
            ->call('verify')
            ->assertHasNoErrors()
            ->assertRedirect(route('esign.sign.consent', $request->public_id));

        $request->refresh();
        expect($request->guest_verified_at)->not->toBeNull();
        expect(GuestSignerSession::isVerified($request))->toBeTrue();

        // 3. Consent, recorded against the guest email with no user id.
        Livewire::test(SignConsent::class, ['request' => $request->fresh()])
            ->set('acknowledged', true)
            ->call('accept')
            ->assertRedirect(route('esign.sign.review', $request->public_id));

        $consent = EsignConsent::query()->latest('id')->firstOrFail();
        expect($consent->user_id)->toBeNull();
        expect($consent->guest_email)->toBe('vendor@example.com');
        expect($consent->consent_scope)->toBe('lien_waivers');
        expect($request->fresh()->consent_id)->toBe($consent->id);

        // 4. Review + sign with the typed legal name only, no signature image.
        Livewire::test(SignReview::class, ['request' => $request->fresh()])
            ->set('adoptedName', 'Vera Vendor')
            ->call('signAll')
            ->assertRedirect(route('esign.sign.done', $request->public_id));

        $request->refresh()->load('documents');
        expect($request->status)->toBe(SignatureRequestStatus::Completed);
        expect($request->adopted_name)->toBe('Vera Vendor');
        expect($request->user_signature_id)->toBeNull();
        expect($request->signature_method)->toBe('typed_name');
        // The OTP verification timestamp is the guest's email-control proof.
        expect($request->email_verified_at_sign)->not->toBeNull();
        expect($request->email_verified_at_sign->format('Y-m-d H:i:s'))
            ->toBe($request->guest_verified_at->format('Y-m-d H:i:s'));

        $document = $request->documents->first();
        expect($document->signed_document_hash)->not->toBeNull();
        expect($document->signedMedia())->not->toBeNull();
        expect(Storage::disk('s3')->exists($document->signedMedia()->getPathRelativeToRoot()))->toBeTrue();
        expect($document->signed_document_hash)->not->toBe($document->locked_document_hash);

        $types = $request->events()->pluck('event_type')
            ->map(fn ($t) => $t instanceof BackedEnum ? $t->value : $t)->all();
        expect($types)->toContain('signer_identity_verified', 'consent_accepted', 'signature_completed', 'final_pdf_generated');

        // The waiver is signed and both sides get the signed copy.
        expect($waiver->refresh()->status)->toBe(WaiverStatus::Signed);
        Mail::assertQueued(WaiverCompleted::class, fn (WaiverCompleted $mail) => $mail->hasTo('vendor@example.com'));
        Mail::assertQueued(WaiverCompleted::class, fn (WaiverCompleted $mail) => $mail->hasTo($creator->email));
    });
});

describe('claiming guest signatures at login', function () {
    it('claims completed guest requests for the matching email, leaving active and mismatched ones alone', function () {
        $user = User::factory()->create(['email' => 'vendor@example.com', 'email_verified_at' => now()]);

        $completed = guestSigningRequest([
            'status' => SignatureRequestStatus::Completed,
            'completed_at' => now(),
            'signer_email_snapshot' => 'vendor@example.com',
        ]);
        $active = guestSigningRequest([
            'signer_email_snapshot' => 'vendor@example.com',
        ]);
        $otherEmail = guestSigningRequest([
            'status' => SignatureRequestStatus::Completed,
            'completed_at' => now(),
            'signer_email_snapshot' => 'someone-else@example.com',
        ]);

        event(new Login('web', $user, false));

        expect($completed->fresh()->signer_user_id)->toBe($user->id);
        expect($active->fresh()->signer_user_id)->toBeNull();
        expect($otherEmail->fresh()->signer_user_id)->toBeNull();
    });

    it('refuses to claim onto an account whose email is not verified', function () {
        $squatter = User::factory()->create([
            'email' => 'vendor@example.com',
            'email_verified_at' => null,
        ]);

        $completed = guestSigningRequest([
            'status' => SignatureRequestStatus::Completed,
            'completed_at' => now(),
            'signer_email_snapshot' => 'vendor@example.com',
        ]);

        event(new Login('web', $squatter, false));

        expect($completed->fresh()->signer_user_id)->toBeNull();
    });
});

describe('account-mode regression', function () {
    it('still requires login and the right account for account signers', function () {
        $signer = User::factory()->create(['email_verified_at' => now()]);
        $request = guestSigningRequest(['signer_user_id' => $signer->id]);

        // Logged out → login.
        $this->get(route('esign.sign.review', $request->public_id))
            ->assertRedirect(route('login'));

        // Wrong account → 403.
        $this->actingAs(User::factory()->create(['email_verified_at' => now()]))
            ->get(route('esign.sign.review', $request->public_id))
            ->assertForbidden();

        // Account signers never see the guest verify screen.
        $this->actingAs($signer)
            ->get(route('esign.sign.verify', $request->public_id))
            ->assertRedirect(route('esign.sign.review', $request->public_id));
    });

    it('lists completed requests (account and claimed-by-email guest) on my-documents', function () {
        $signer = User::factory()->create(['email_verified_at' => now()]);

        $account = guestSigningRequest([
            'signer_user_id' => $signer->id,
            'status' => SignatureRequestStatus::Completed,
            'completed_at' => now(),
        ]);
        SignatureDocument::create([
            'signature_request_id' => $account->id,
            'document_identifier' => 'LW-1001',
            'label' => 'Conditional Waiver / Riverfront Plaza',
            'sort_order' => 0,
            'signed_at' => now(),
        ]);

        $guest = guestSigningRequest([
            'signer_email_snapshot' => $signer->email,
            'status' => SignatureRequestStatus::Completed,
            'completed_at' => now(),
        ]);
        SignatureDocument::create([
            'signature_request_id' => $guest->id,
            'document_identifier' => 'LW-1001',
            'label' => 'Unconditional Waiver / Harbor Tower',
            'sort_order' => 0,
            'signed_at' => now(),
        ]);

        $this->actingAs($signer)
            ->get(route('esign.mine'))
            ->assertOk()
            ->assertSee('Conditional Waiver / Riverfront Plaza')
            ->assertSee('Unconditional Waiver / Harbor Tower');
    });
});
