<?php

namespace App\Domains\Esign\Livewire;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Livewire\Concerns\GuardsSigningSession;
use App\Domains\Esign\Models\EsignConsent;
use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Component;

class SignConsent extends Component
{
    use GuardsSigningSession;

    public SignatureRequest $request;

    public bool $acknowledged = false;

    public function mount(SignatureRequest $request): void
    {
        $this->guardSigner($request);
        $this->request = $request;

        if ($request->isCompleted()) {
            $this->redirectRoute('esign.sign.done', ['request' => $request->public_id], navigate: true);

            return;
        }

        if ($this->currentConsent($request) !== null) {
            $this->redirectRoute('esign.sign.review', ['request' => $request->public_id], navigate: true);
        }
    }

    public function accept()
    {
        $this->guardSigner($this->request);

        $this->validate(
            ['acknowledged' => ['accepted']],
            ['acknowledged.accepted' => 'Please confirm you can access electronic records and agree to sign electronically.'],
        );

        $config = config('esign.consent');
        $policy = DocumentSigningPolicy::for($this->request->document_signing_policy_key);

        $consent = EsignConsent::create([
            'user_id' => auth()->id(),
            'consent_scope' => $policy->consentScope(),
            'version' => $config['version'],
            'disclosure_text' => $this->fullConsentText($config),
            'disclosure_snapshot_json' => $this->consentSnapshot($config),
            'hardware_software_ack' => true,
            'consented_ip' => request()->ip(),
            'consented_user_agent' => request()->userAgent(),
            'consented_at' => Carbon::now(),
        ]);

        $this->request->update(['consent_id' => $consent->id]);

        app(AppendSignatureEvent::class)->execute($this->request, SignatureEventType::ConsentAccepted,
            actorType: 'signer', actorUserId: auth()->id(), ip: request()->ip(), userAgent: request()->userAgent(),
            metadata: ['consent_id' => $consent->id, 'scope' => $policy->consentScope(), 'version' => $config['version']]);

        return $this->redirectRoute('esign.sign.review', ['request' => $this->request->public_id], navigate: true);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function fullConsentText(array $config): string
    {
        $lines = [$config['heading'], '', $config['agreement'], ''];

        foreach ($config['disclosures'] as $disclosure) {
            $lines[] = $disclosure;
            $lines[] = '';
        }

        $lines[] = $config['checkbox'];

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function consentSnapshot(array $config): array
    {
        return [
            'heading' => $config['heading'],
            'agreement' => $config['agreement'],
            'checkbox' => $config['checkbox'],
            'accept_button' => $config['accept_button'],
            'disclosures' => $config['disclosures'],
        ];
    }

    public function render(): View
    {
        return view('livewire.esign.sign-consent', [
            'consent' => config('esign.consent'),
        ])->layout('layouts.minimal');
    }
}
