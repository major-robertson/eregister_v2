<?php

namespace App\Domains\Esign\Livewire;

use App\Domains\Esign\Livewire\Concerns\GuardsSigningSession;
use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SignDone extends Component
{
    use GuardsSigningSession;

    public SignatureRequest $request;

    public function mount(SignatureRequest $request): void
    {
        $this->guardSigner($request);
        $this->request = $request->load('documents');

        if (! $request->isCompleted()) {
            $this->redirectRoute('esign.sign.review', ['request' => $request->public_id], navigate: true);
        }
    }

    public function render(): View
    {
        return view('livewire.esign.sign-done', [
            'documents' => $this->request->documents,
            'title' => config("esign.document_types.{$this->request->document_signing_policy_key}.title", 'Documents'),
            'isGuest' => $this->request->isGuest(),
            'registerUrl' => route('register').'?'.http_build_query(array_filter([
                'email' => $this->request->signer_email_snapshot,
            ])),
        ])->layout('layouts.minimal');
    }
}
