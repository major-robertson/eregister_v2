<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\Esign\Actions\AdoptSignature;
use App\Domains\Esign\Models\EsignConsent;
use App\Domains\ResaleCert\Actions\AppendResaleSignatureEvent;
use App\Domains\ResaleCert\Enums\ResaleSignatureEventType;
use App\Models\UserSignature;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Signature adoption for the resale workspace: type your name (script font
 * of choice) or draw, both exported to the same 500x100 PNG that gets
 * stamped onto every certificate. Site-wide: the adopted signature is also
 * used by esign flows (lien demand letters).
 *
 * Adoption follows the Esign standard: verified email required, ESIGN/UETA
 * consent snapshotted onto esign_consents (scope sales_tax_resale_certs),
 * and both consent and adoption land in the hash-chained resale audit log
 * with IP + user agent.
 */
class SignaturePad extends Component
{
    public function save(
        string $signatureImage,
        ?string $strokesJson,
        bool $agreedToTerms,
        string $method = UserSignature::METHOD_DRAWN,
        ?string $typedName = null,
        ?string $typedFont = null,
    ): void {
        $user = Auth::user();
        $business = $user->currentBusiness();

        if (! $business) {
            throw ValidationException::withMessages([
                'signature' => 'No current business selected.',
            ]);
        }

        if (! $agreedToTerms) {
            throw ValidationException::withMessages([
                'signature' => 'You must agree to the electronic signature disclosures.',
            ]);
        }

        $previousSignatureId = $user->currentSignature()->value('id');

        DB::transaction(function () use ($user, $business, $signatureImage, $strokesJson, $method, $typedName, $typedFont, $previousSignatureId) {
            $consent = $this->ensureConsent($business->id);

            $signature = app(AdoptSignature::class)->execute(
                $user,
                $method,
                $signatureImage,
                strokesJson: $strokesJson,
                typedName: $typedName,
                typedFont: $typedFont,
            );

            app(AppendResaleSignatureEvent::class)->execute(
                $business,
                ResaleSignatureEventType::SignatureAdopted,
                signatureId: $signature->id,
                actorUserId: $user->id,
                ip: request()->ip(),
                userAgent: request()->userAgent(),
                metadata: array_filter([
                    'method' => $signature->method,
                    'typed_font' => $signature->typed_font,
                    'image_sha256' => AdoptSignature::imageSha256($signature),
                    'stroke_count' => $signature->strokes_json === null ? null : count($signature->strokes_json),
                    'consent_id' => $consent->id,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                    'replaced_signature_id' => $previousSignatureId,
                ], fn ($value) => $value !== null),
            );
        });

        unset($this->currentSignature);

        $this->dispatch('resale-signature-saved');
    }

    /**
     * The current ESIGN consent for the resale scope, creating (and auditing)
     * one from the checkbox acceptance when none exists at this version.
     */
    protected function ensureConsent(int $businessId): EsignConsent
    {
        $user = Auth::user();
        $config = config('esign.consent');
        $scope = config('esign.document_types.resale_certificate.consent_scope');

        if ($existing = EsignConsent::currentFor($user, $scope, $config['version'])) {
            return $existing;
        }

        $consent = EsignConsent::create([
            'user_id' => $user->id,
            'consent_scope' => $scope,
            'version' => $config['version'],
            'disclosure_text' => $this->fullConsentText($config),
            'disclosure_snapshot_json' => [
                'heading' => $config['heading'],
                'agreement' => $config['agreement'],
                'checkbox' => $config['checkbox'],
                'disclosures' => $config['disclosures'],
            ],
            'hardware_software_ack' => true,
            'consented_ip' => request()->ip(),
            'consented_user_agent' => request()->userAgent(),
            'consented_at' => now(),
        ]);

        app(AppendResaleSignatureEvent::class)->execute(
            $businessId,
            ResaleSignatureEventType::ConsentAccepted,
            actorUserId: $user->id,
            ip: request()->ip(),
            userAgent: request()->userAgent(),
            metadata: ['consent_id' => $consent->id, 'scope' => $scope, 'version' => $config['version']],
        );

        return $consent;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected function fullConsentText(array $config): string
    {
        $lines = [$config['heading'], '', $config['agreement'], ''];

        foreach ($config['disclosures'] as $disclosure) {
            $lines[] = $disclosure;
            $lines[] = '';
        }

        $lines[] = $config['checkbox'];

        return implode("\n", $lines);
    }

    public function resendVerification(): void
    {
        $user = Auth::user();

        if ($user->email_verified_at === null) {
            // Return the user to this page after they click the emailed link.
            session()->put('url.intended', url()->previous() ?: route('resale-cert.onboarding'));

            $user->sendEmailVerificationNotification();
        }

        Flux::toast(text: 'Verification email sent to '.$user->email, variant: 'success');
    }

    #[Computed]
    public function emailVerified(): bool
    {
        return Auth::user()->email_verified_at !== null;
    }

    #[Computed]
    public function currentSignature(): ?UserSignature
    {
        return Auth::user()->currentSignature;
    }

    #[Computed]
    public function currentSignatureDataUri(): ?string
    {
        return $this->currentSignature?->imageDataUri();
    }

    public function render(): View
    {
        return view('livewire.resale-cert.signature-pad', [
            'consent' => config('esign.consent'),
        ]);
    }
}
