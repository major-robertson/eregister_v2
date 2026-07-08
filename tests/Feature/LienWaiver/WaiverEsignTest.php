<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Actions\CompleteSignature;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\SignableResolver;
use App\Domains\Lien\Documents\WaiverGenerator;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Esign\Actions\SendWaiverForSignature;
use App\Domains\Lien\Esign\Actions\VoidWaiverSignatureRequest;
use App\Domains\Lien\Esign\LienWaiverSignable;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Mail\WaiverCompleted;
use App\Mail\WaiverSignatureInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    Mail::fake();
});

/**
 * A generated waiver with a creator and (optionally) a frozen render snapshot.
 * CO uses the generic house forms with e-sign allowed and no deemed-effective
 * statute, so it exercises the default path.
 */
function waiverEsignReady(array $overrides = [], string $state = 'CO', bool $collect = false, bool $snapshot = true): LienWaiver
{
    $business = Business::factory()->create();
    $creator = User::factory()->create(['email_verified_at' => now()]);
    $project = LienProject::factory()->forBusiness($business)->inState($state)->create();

    $factory = LienWaiver::factory()->forProject($project);

    if ($collect) {
        $factory = $factory->collect();
    }

    $waiver = $factory->generated()->create(array_merge([
        'created_by_user_id' => $creator->id,
    ], $overrides));

    if ($snapshot) {
        $waiver->update(['render_snapshot_json' => app(WaiverGenerator::class)->data($waiver)]);
    }

    return $waiver->fresh();
}

/** Fabricate a signature request on a waiver without rendering any PDF. */
function waiverEsignFabricatedRequest(LienWaiver $waiver, array $overrides = []): SignatureRequest
{
    return SignatureRequest::create(array_merge([
        'signable_type' => 'lien_waiver',
        'signable_id' => $waiver->id,
        'business_id' => $waiver->business_id,
        'signer_user_id' => null,
        'document_signing_policy_key' => LienWaiverSignable::DOCUMENT_TYPE,
        'status' => SignatureRequestStatus::AwaitingSignature,
        'signer_name_snapshot' => $waiver->signer_name ?? $waiver->counterparty_name,
        'signer_email_snapshot' => $waiver->signer_email ?? $waiver->counterparty_email,
        'invited_at' => now(),
        'expires_at' => now()->addDays(14),
    ], $overrides));
}

describe('SendWaiverForSignature', function () {
    it('sends a provide-direction waiver with the creator as the account signer', function () {
        $waiver = waiverEsignReady();
        $creator = $waiver->createdBy;

        $request = app(SendWaiverForSignature::class)->execute($waiver, $creator);

        expect($request->status)->toBe(SignatureRequestStatus::AwaitingSignature);
        expect($request->signer_user_id)->toBe($creator->id);
        expect($request->signer_email_snapshot)->toBe($creator->email);
        expect($request->document_signing_policy_key)->toBe('lien_waiver');
        expect($request->invited_at)->not->toBeNull();
        expect($request->expires_at->isSameDay(now()->addDays(14)))->toBeTrue();

        // Exactly one locked + hashed document.
        expect($request->documents()->count())->toBe(1);
        $document = $request->documents()->first();
        expect($document->document_identifier)->toBe('LW-1001');
        expect($document->locked_document_hash)->not->toBeNull();
        expect($document->locked_at)->not->toBeNull();
        expect($document->lockedMedia())->not->toBeNull();
        expect($document->document_snapshot_json['meta']['unsigned_pdf_hash'])->toBe($document->locked_document_hash);

        $types = $request->events()->pluck('event_type')
            ->map(fn ($t) => $t instanceof BackedEnum ? $t->value : $t)->all();
        expect($types)->toContain('document_created', 'document_locked', 'signer_invited');

        Mail::assertQueued(WaiverSignatureInvitation::class,
            fn (WaiverSignatureInvitation $mail) => $mail->hasTo($creator->email) && ! $mail->isGuest);

        $waiver->refresh();
        expect($waiver->status)->toBe(WaiverStatus::AwaitingSignature);
        expect($waiver->sent_at)->not->toBeNull();
    });

    it('sends a collect-direction waiver as a guest session snapshotting the waiver signer fields', function () {
        $waiver = waiverEsignReady(overrides: [
            'signer_name' => 'Vera Vendor',
            'signer_email' => 'vendor@example.com',
            'counterparty_phone' => '619-555-0142',
        ], collect: true);

        $request = app(SendWaiverForSignature::class)->execute($waiver, $waiver->createdBy);

        expect($request->signer_user_id)->toBeNull();
        expect($request->signer_name_snapshot)->toBe('Vera Vendor');
        expect($request->signer_email_snapshot)->toBe('vendor@example.com');
        expect($request->signer_phone_snapshot)->toBe('619-555-0142');

        Mail::assertQueued(WaiverSignatureInvitation::class,
            fn (WaiverSignatureInvitation $mail) => $mail->hasTo('vendor@example.com') && $mail->isGuest);
    });

    it('refuses states where the statutory form must be executed in person (GA)', function () {
        $waiver = waiverEsignReady(state: 'GA', snapshot: false);

        expect(fn () => app(SendWaiverForSignature::class)->execute($waiver, $waiver->createdBy))
            ->toThrow(EsignException::class, 'hand and seal');

        expect($waiver->fresh()->status)->toBe(WaiverStatus::Generated);
        Mail::assertNotQueued(WaiverSignatureInvitation::class);
    });

    it('requires a generated waiver with a frozen snapshot', function () {
        // Generated status but never actually rendered (no snapshot).
        $noSnapshot = waiverEsignReady(snapshot: false);
        expect(fn () => app(SendWaiverForSignature::class)->execute($noSnapshot, $noSnapshot->createdBy))
            ->toThrow(EsignException::class, 'Generate the waiver PDF');

        // Still a draft.
        $draft = waiverEsignReady(overrides: ['status' => WaiverStatus::Draft]);
        expect(fn () => app(SendWaiverForSignature::class)->execute($draft, $draft->createdBy))
            ->toThrow(EsignException::class, 'Generate the waiver PDF');
    });

    it('rejects a second send while a signature request is active', function () {
        $waiver = waiverEsignReady();
        waiverEsignFabricatedRequest($waiver);

        expect(fn () => app(SendWaiverForSignature::class)->execute($waiver, $waiver->createdBy))
            ->toThrow(EsignException::class, 'active signature request');
    });

    it('requires a signer email on collect-direction waivers', function () {
        $waiver = waiverEsignReady(overrides: ['signer_email' => null], collect: true);

        expect(fn () => app(SendWaiverForSignature::class)->execute($waiver, $waiver->createdBy))
            ->toThrow(EsignException::class, 'email address before sending');
    });
});

describe('completion', function () {
    it('marks the waiver signed, leaves deemed-effective unset for non-statute states, and mails both parties', function () {
        $waiver = waiverEsignReady(overrides: ['counterparty_email' => 'customer@example.com']);
        $creator = $waiver->createdBy;

        $request = app(SendWaiverForSignature::class)->execute($waiver, $creator);

        app(CompleteSignature::class)->execute(
            $request->fresh('documents'),
            $creator,
            $creator->name,
            ['intent_statement' => config('esign.document_types.lien_waiver.intent'), 'document_list_snapshot' => []],
        );

        $request->refresh();
        expect($request->status)->toBe(SignatureRequestStatus::Completed);
        expect($request->completed_at)->not->toBeNull();

        $document = $request->documents()->first();
        expect($document->signed_document_hash)->not->toBeNull();
        expect($document->signedMedia())->not->toBeNull();

        $waiver->refresh();
        expect($waiver->status)->toBe(WaiverStatus::Signed);
        expect($waiver->signed_at)->not->toBeNull();
        // CO has no deemed-effective statute; the countdown stays unset.
        expect($waiver->deemed_effective_at)->toBeNull();

        // Both the counterparty and the requesting business get the signed copy.
        Mail::assertQueued(WaiverCompleted::class, fn (WaiverCompleted $mail) => $mail->hasTo('customer@example.com'));
        Mail::assertQueued(WaiverCompleted::class, fn (WaiverCompleted $mail) => $mail->hasTo($creator->email));
        Mail::assertQueued(WaiverCompleted::class, 2);
    });

    it('computes the GA 90-day deemed-effective date via onCompleted (registry math, no PDF)', function () {
        // GA disallows e-sign end-to-end, so exercise the deemed-effective math
        // directly against the signable adapter with a fabricated completed request.
        $waiver = waiverEsignReady(state: 'GA', snapshot: false);
        $request = waiverEsignFabricatedRequest($waiver, [
            'status' => SignatureRequestStatus::Completed,
            'completed_at' => now(),
        ]);

        app(SignableResolver::class)->for($waiver)->onCompleted($request);

        $waiver->refresh();
        expect($waiver->status)->toBe(WaiverStatus::Signed);
        expect($waiver->signed_at)->not->toBeNull();
        expect($waiver->deemed_effective_at)->not->toBeNull();
        // Anchored on the Eastern calendar date (statutory countdown is a
        // calendar rule), consistent with how signed_at is displayed.
        expect($waiver->deemed_effective_at->toDateString())->toBe(now()->eastern()->addDays(90)->toDateString());
    });
});

describe('VoidWaiverSignatureRequest', function () {
    it('voids the active request and returns the waiver to Generated', function () {
        $waiver = waiverEsignReady();
        $waiver->update(['status' => WaiverStatus::AwaitingSignature, 'sent_at' => now()]);
        $request = waiverEsignFabricatedRequest($waiver);

        app(VoidWaiverSignatureRequest::class)->execute($waiver->fresh(), $waiver->createdBy, 'Wrong amount');

        $request->refresh();
        expect($request->status)->toBe(SignatureRequestStatus::Voided);
        expect($request->voided_at)->not->toBeNull();
        expect($request->failure_reason)->toBe('Wrong amount');

        $types = $request->events()->pluck('event_type')
            ->map(fn ($t) => $t instanceof BackedEnum ? $t->value : $t)->all();
        expect($types)->toContain('signature_voided');

        $waiver->refresh();
        expect($waiver->status)->toBe(WaiverStatus::Generated);
        expect($waiver->sent_at)->toBeNull();
    });

    it('still resets an awaiting waiver when no active request exists', function () {
        $waiver = waiverEsignReady();
        $waiver->update(['status' => WaiverStatus::AwaitingSignature, 'sent_at' => now()]);

        app(VoidWaiverSignatureRequest::class)->execute($waiver->fresh(), $waiver->createdBy);

        expect($waiver->fresh()->status)->toBe(WaiverStatus::Generated);
    });
});
