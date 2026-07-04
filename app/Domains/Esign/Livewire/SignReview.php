<?php

namespace App\Domains\Esign\Livewire;

use App\Domains\Esign\Actions\AdoptSignature;
use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Actions\CompleteSignature;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Livewire\Concerns\GuardsSigningSession;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\SignableResolver;
use App\Models\UserSignature;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SignReview extends Component
{
    use GuardsSigningSession;

    public SignatureRequest $request;

    public string $adoptedName = '';

    /**
     * Apply the signer's saved site-wide signature instead of capturing a
     * new one. Defaults on when a saved signature exists.
     */
    public bool $useSaved = false;

    public function mount(SignatureRequest $request): void
    {
        $this->guardSigner($request);
        $this->request = $request->load('documents');

        if ($request->isCompleted()) {
            $this->redirectRoute('esign.sign.done', ['request' => $request->public_id], navigate: true);

            return;
        }

        if ($this->currentConsent($request) === null) {
            $this->redirectRoute('esign.sign.consent', ['request' => $request->public_id], navigate: true);

            return;
        }

        $this->adoptedName = (string) (auth()->user()->name ?? '');
        $this->useSaved = $this->savedSignature !== null;

        // Record the first review-screen view once.
        if (! $request->events()->where('event_type', SignatureEventType::DocumentViewed->value)->exists()) {
            app(AppendSignatureEvent::class)->execute($request, SignatureEventType::DocumentViewed,
                actorType: 'signer', actorUserId: auth()->id(), ip: request()->ip(), userAgent: request()->userAgent());
        }
    }

    #[Computed]
    public function savedSignature(): ?UserSignature
    {
        return auth()->user()->currentSignature;
    }

    #[Computed]
    public function savedSignatureDataUri(): ?string
    {
        return $this->savedSignature?->imageDataUri();
    }

    /**
     * @param  string|null  $signatureImage  PNG data URI from the capture UI (null when using the saved signature).
     */
    public function signAll(
        ?string $signatureImage = null,
        ?string $strokesJson = null,
        ?string $method = null,
        ?string $typedFont = null,
    ) {
        $this->guardSigner($this->request);

        $this->validate(
            ['adoptedName' => ['required', 'string', 'max:255']],
            ['adoptedName.required' => 'Type your full legal name to sign.'],
        );

        $request = $this->request->fresh('documents');

        if ($request->isCompleted()) {
            return $this->redirectRoute('esign.sign.done', ['request' => $request->public_id], navigate: true);
        }

        if ($this->currentConsent($request) === null) {
            return $this->redirectRoute('esign.sign.consent', ['request' => $request->public_id], navigate: true);
        }

        $signature = $this->resolveSignature($signatureImage, $strokesJson, $method, $typedFont);

        app(CompleteSignature::class)->execute(
            $request,
            auth()->user(),
            trim($this->adoptedName),
            $this->presentedText($request),
            $signature,
        );

        $signable = app(SignableResolver::class)->for($request->refresh()->signable);

        return $this->redirect($signable->postSignRedirectRoute($request));
    }

    /**
     * The saved site-wide signature, or a freshly captured one — which is
     * adopted as the signer's new site-wide signature so resale certs and
     * future signings reuse it.
     */
    protected function resolveSignature(?string $signatureImage, ?string $strokesJson, ?string $method, ?string $typedFont): ?UserSignature
    {
        if ($this->useSaved && $this->savedSignature) {
            return $this->savedSignature;
        }

        if (blank($signatureImage)) {
            throw ValidationException::withMessages([
                'adoptedName' => 'Draw or type your signature before signing.',
            ]);
        }

        return app(AdoptSignature::class)->execute(
            auth()->user(),
            $method ?? UserSignature::METHOD_TYPED,
            $signatureImage,
            strokesJson: $strokesJson,
            typedName: trim($this->adoptedName),
            typedFont: $typedFont,
        );
    }

    /**
     * Snapshot the exact UI strings + document list the signer saw when signing.
     *
     * @return array<string, mixed>
     */
    private function presentedText(SignatureRequest $request): array
    {
        return [
            'intent_statement' => config('esign.signing.intent'),
            'sign_button' => config('esign.signing.sign_button'),
            'typed_name_label' => config('esign.signing.typed_name_label'),
            'document_list_snapshot' => $request->documents->map(fn ($document) => [
                'document_identifier' => $document->document_identifier,
                'label' => $document->label,
                'locked_hash' => $document->locked_document_hash,
            ])->values()->all(),
        ];
    }

    public function render(): View
    {
        return view('livewire.esign.sign-review', [
            'documents' => $this->request->documents,
            'intent' => config('esign.signing.intent'),
            'signButton' => config('esign.signing.sign_button'),
            'typedNameLabel' => config('esign.signing.typed_name_label'),
            'title' => config("esign.document_types.{$this->request->document_signing_policy_key}.title", 'Documents'),
        ])->layout('layouts.minimal');
    }
}
