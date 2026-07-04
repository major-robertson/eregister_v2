<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\ResaleCert\Actions\AppendResaleSignatureEvent;
use App\Domains\ResaleCert\Enums\ResaleSignatureEventType;
use App\Domains\ResaleCert\Livewire\Concerns\ResolvesResaleContext;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleSignatureEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CertificateShow extends Component
{
    use ResolvesResaleContext;

    public ResaleCertificate $certificate;

    public function mount(ResaleCertificate $certificate): void
    {
        if (! $this->resolveBusiness() || ! $this->requireCompleteProfile()) {
            return;
        }

        $this->certificate = $certificate->load(['vendor', 'stateRule', 'createdBy']);
    }

    /**
     * Audit trail for this certificate (generation, downloads, deletion).
     *
     * @return Collection<int, ResaleSignatureEvent>
     */
    #[Computed]
    public function auditEvents(): Collection
    {
        return ResaleSignatureEvent::query()
            ->where('business_id', $this->business->id)
            ->where('resale_certificate_id', $this->certificate->id)
            ->with('actor')
            ->orderBy('id')
            ->get();
    }

    public function deleteCertificate(): void
    {
        app(AppendResaleSignatureEvent::class)->execute(
            $this->certificate->business_id,
            ResaleSignatureEventType::CertificateDeleted,
            certificateId: $this->certificate->id,
            actorUserId: auth()->id(),
            ip: request()->ip(),
            userAgent: request()->userAgent(),
            metadata: array_filter([
                'state_code' => $this->certificate->state_code,
                'pdf_sha256' => $this->certificate->pdf_sha256,
            ]),
        );

        $this->certificate->deletePdfFile();
        $this->certificate->update(['pdf_path' => null]);
        $this->certificate->delete();

        session()->flash('success', 'Certificate deleted.');

        $this->redirect(route('resale-cert.certificates.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.resale-cert.certificate-show')
            ->layout('components.layouts.portal', ['title' => 'Certificate #'.$this->certificate->id]);
    }
}
