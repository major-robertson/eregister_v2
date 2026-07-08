<?php

namespace App\Domains\Esign\Livewire;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Actions\SendGuestSignerCode;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\Support\GuestSignerSession;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * Guest signer email challenge. Control of the invited inbox is the identity
 * assertion for account-less signing (the E-SIGN/UETA norm): we email a
 * one-time code, the signer types it back, and the proof + method land in the
 * audit chain.
 */
class SignVerifyIdentity extends Component
{
    public SignatureRequest $request;

    public string $code = '';

    public function mount(SignatureRequest $request): void
    {
        abort_if($request->signable === null, 404);

        // Account-mode sessions never see this page.
        if ($request->signer_user_id !== null) {
            $this->redirectRoute('esign.sign.review', ['request' => $request->public_id], navigate: true);

            return;
        }

        $this->request = $request;

        if (GuestSignerSession::isVerified($request)) {
            $this->redirectAfterVerification();

            return;
        }

        abort_if($request->status === SignatureRequestStatus::Voided, 410, 'This signing request has been voided.');
        abort_if(! $request->isCompleted() && $request->isExpired(), 410, 'This signing link has expired.');

        // Codes are only issued to sessions that came through the emailed
        // signed URL; knowing the public id alone must not let anyone
        // trigger emails to the signer.
        if (! GuestSignerSession::isChallenged($request)) {
            return;
        }

        // First arrival (or an expired code): issue one automatically.
        if ($request->guest_code_hash === null || $request->guest_code_expires_at?->isPast()) {
            app(SendGuestSignerCode::class)->execute($request);
        }
    }

    public function verify(): void
    {
        $this->validate(
            ['code' => ['required', 'digits:6']],
            ['code.digits' => 'Enter the 6-digit code from your email.'],
        );

        $request = $this->request->fresh();

        if ($request->guest_code_attempts >= SendGuestSignerCode::MAX_ATTEMPTS) {
            $this->addError('code', 'Too many attempts. Send a new code and try again.');

            return;
        }

        $request->increment('guest_code_attempts');

        $expired = $request->guest_code_expires_at === null || $request->guest_code_expires_at->isPast();
        $matches = $request->guest_code_hash !== null
            && hash_equals($request->guest_code_hash, hash('sha256', trim($this->code)));

        if ($expired || ! $matches) {
            $this->addError('code', $expired
                ? 'That code has expired. Send a new code and try again.'
                : 'That code doesn\'t match. Check your email and try again.');

            return;
        }

        if ($request->guest_verified_at === null) {
            $request->update(['guest_verified_at' => Carbon::now()]);
        }

        // Clear the used code so it can't be replayed from another session.
        $request->update(['guest_code_hash' => null, 'guest_code_expires_at' => null]);

        GuestSignerSession::markVerified($request);

        app(AppendSignatureEvent::class)->execute($request, SignatureEventType::SignerIdentityVerified,
            actorType: 'signer', ip: request()->ip(), userAgent: request()->userAgent(),
            metadata: ['method' => 'email_otp', 'email' => $request->signer_email_snapshot]);

        if ($request->first_opened_at === null) {
            $request->update(['first_opened_at' => Carbon::now()]);

            app(AppendSignatureEvent::class)->execute($request, SignatureEventType::SignerOpened,
                actorType: 'signer', ip: request()->ip(), userAgent: request()->userAgent());
        }

        $this->request = $request;
        $this->redirectAfterVerification();
    }

    public function resend(): void
    {
        if (! GuestSignerSession::isChallenged($this->request)) {
            $this->addError('code', 'Open the signing link from your email to request a code.');

            return;
        }

        $sent = app(SendGuestSignerCode::class)->execute($this->request->fresh());

        if ($sent) {
            session()->flash('esign_code_resent', true);
        } else {
            $this->addError('code', 'A code was sent recently or the limit was reached. Check your inbox, or ask the sender to re-issue the request.');
        }
    }

    private function redirectAfterVerification(): void
    {
        $request = $this->request;

        if ($request->isCompleted()) {
            $this->redirectRoute('esign.sign.done', ['request' => $request->public_id], navigate: true);

            return;
        }

        $needsConsent = $request->consent === null
            || $request->consent->consent_scope !== $request->policy()->consentScope()
            || $request->consent->version !== config('esign.consent.version');

        $this->redirectRoute(
            $needsConsent ? 'esign.sign.consent' : 'esign.sign.review',
            ['request' => $request->public_id],
            navigate: true,
        );
    }

    public function maskedEmail(): string
    {
        $email = (string) $this->request->signer_email_snapshot;

        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible.str_repeat('•', max(1, mb_strlen($local) - mb_strlen($visible))).'@'.$domain;
    }

    public function render(): View
    {
        return view('livewire.esign.sign-verify-identity', [
            'maskedEmail' => $this->maskedEmail(),
            'title' => config("esign.document_types.{$this->request->document_signing_policy_key}.title", 'Documents'),
            'linkRequired' => ! GuestSignerSession::isChallenged($this->request),
        ])->layout('layouts.minimal');
    }
}
