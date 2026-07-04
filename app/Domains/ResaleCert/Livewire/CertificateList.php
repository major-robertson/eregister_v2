<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\ResaleCert\Actions\AppendResaleSignatureEvent;
use App\Domains\ResaleCert\Enums\ResaleSignatureEventType;
use App\Domains\ResaleCert\Livewire\Concerns\ResolvesResaleContext;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleStateRule;
use App\Domains\ResaleCert\Models\ResaleVendor;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateList extends Component
{
    use ResolvesResaleContext, WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $vendorFilter = '';

    #[Url]
    public string $stateFilter = '';

    #[Url]
    public string $statusFilter = '';

    public function mount(): void
    {
        if (! $this->resolveBusiness()) {
            return;
        }

        $this->requireCompleteProfile();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingVendorFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStateFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    protected function query(): Builder
    {
        return ResaleCertificate::query()
            ->with(['vendor', 'stateRule', 'createdBy'])
            ->when($this->vendorFilter !== '', fn ($q) => $q->where('resale_vendor_id', $this->vendorFilter))
            ->when($this->stateFilter !== '', fn ($q) => $q->where('state_code', $this->stateFilter))
            ->when($this->statusFilter === 'active', function ($q) {
                $q->where(fn ($sub) => $sub->whereNull('expiration_date')->orWhere('expiration_date', '>', now()));
            })
            ->when($this->statusFilter === 'expiring', function ($q) {
                $q->whereNotNull('expiration_date')->whereBetween('expiration_date', [now(), now()->addDays(90)]);
            })
            ->when($this->statusFilter === 'expired', function ($q) {
                $q->whereNotNull('expiration_date')->where('expiration_date', '<', now());
            })
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('vendor', fn ($vq) => $vq->where('legal_name', 'like', "%{$this->search}%"))
                        ->orWhere('item_description', 'like', "%{$this->search}%")
                        ->orWhere('id', 'like', "%{$this->search}%");
                });
            });
    }

    public function deleteCertificate(int $certificateId): void
    {
        $certificate = ResaleCertificate::findOrFail($certificateId);

        app(AppendResaleSignatureEvent::class)->execute(
            $certificate->business_id,
            ResaleSignatureEventType::CertificateDeleted,
            certificateId: $certificate->id,
            actorUserId: auth()->id(),
            ip: request()->ip(),
            userAgent: request()->userAgent(),
            metadata: array_filter([
                'state_code' => $certificate->state_code,
                'pdf_sha256' => $certificate->pdf_sha256,
            ]),
        );

        $certificate->deletePdfFile();
        $certificate->update(['pdf_path' => null]);
        $certificate->delete();

        Flux::toast(text: 'Certificate deleted.', variant: 'success');
    }

    public function exportCsv(): StreamedResponse
    {
        $certificates = $this->query()->orderByDesc('created_at')->get();

        $filename = 'resale_certificates_'.now()->eastern()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($certificates) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Certificate ID', 'Vendor', 'State/Form', 'Type', 'Issue Date',
                'Expiration Date', 'Status', 'Description', 'Created By', 'Created At',
            ]);

            foreach ($certificates as $certificate) {
                fputcsv($handle, [
                    $certificate->id,
                    $certificate->vendor?->legal_name,
                    $certificate->displayName(),
                    $certificate->is_blanket ? 'Blanket' : 'Specific',
                    $certificate->issue_date->format('Y-m-d'),
                    $certificate->expiration_date?->format('Y-m-d') ?? 'No expiration',
                    $certificate->statusLabel(),
                    $certificate->item_description,
                    $certificate->createdBy?->name,
                    $certificate->created_at->eastern()->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render(): View
    {
        $certificates = $this->query()->orderByDesc('created_at')->paginate(15);
        $vendors = ResaleVendor::query()->orderBy('legal_name')->get(['id', 'legal_name']);
        $states = ResaleStateRule::query()->orderBy('state_name')->get(['state_code', 'state_name']);

        return view('livewire.resale-cert.certificate-list', compact('certificates', 'vendors', 'states'))
            ->layout('components.layouts.portal', ['title' => 'Certificates']);
    }
}
