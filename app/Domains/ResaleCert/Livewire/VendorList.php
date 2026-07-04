<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\ResaleCert\Livewire\Concerns\ResolvesResaleContext;
use App\Domains\ResaleCert\Models\ResaleVendor;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class VendorList extends Component
{
    use ResolvesResaleContext, WithPagination;

    public string $search = '';

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

    public function deleteVendor(int $vendorId): void
    {
        $vendor = ResaleVendor::findOrFail($vendorId);

        if ($vendor->certificates()->exists()) {
            Flux::toast(text: 'Cannot delete a vendor with certificates. Delete its certificates first.', variant: 'warning');

            return;
        }

        $vendor->delete();

        Flux::toast(text: 'Vendor deleted.', variant: 'success');
    }

    public function render(): View
    {
        $vendors = ResaleVendor::query()
            ->withCount('certificates')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('legal_name', 'like', "%{$this->search}%")
                        ->orWhere('contact_name', 'like', "%{$this->search}%")
                        ->orWhere('contact_email', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('legal_name')
            ->paginate(15);

        return view('livewire.resale-cert.vendor-list', compact('vendors'))
            ->layout('components.layouts.portal', ['title' => 'Vendors']);
    }
}
