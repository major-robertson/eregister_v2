<?php

namespace App\Domains\Esign\Livewire;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Actions\CompleteSignature;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Livewire\Concerns\GuardsSigningSession;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\SignableResolver;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SignReview extends Component
{
    use GuardsSigningSession;

    public SignatureRequest $request;

    public string $adoptedName = '';

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

        // Record the first review-screen view once.
        if (! $request->events()->where('event_type', SignatureEventType::DocumentViewed->value)->exists()) {
            app(AppendSignatureEvent::class)->execute($request, SignatureEventType::DocumentViewed,
                actorType: 'signer', actorUserId: auth()->id(), ip: request()->ip(), userAgent: request()->userAgent());
        }
    }

    public function signAll()
    {
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

        app(CompleteSignature::class)->execute(
            $request,
            auth()->user(),
            trim($this->adoptedName),
            $this->presentedText($request),
        );

        $signable = app(SignableResolver::class)->for($request->refresh()->signable);

        return $this->redirect($signable->postSignRedirectRoute($request));
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
