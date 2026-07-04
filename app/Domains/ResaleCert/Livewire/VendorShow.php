<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\ResaleCert\Livewire\Concerns\ResolvesResaleContext;
use App\Domains\ResaleCert\Models\ResaleVendor;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class VendorShow extends Component
{
    use ResolvesResaleContext;

    public ResaleVendor $vendor;

    public function mount(ResaleVendor $vendor): void
    {
        if (! $this->resolveBusiness() || ! $this->requireCompleteProfile()) {
            return;
        }

        $this->vendor = $vendor;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Domains\ResaleCert\Models\ResaleCertificate>
     */
    #[Computed]
    public function certificates(): \Illuminate\Support\Collection
    {
        return $this->vendor->certificates()
            ->with('stateRule')
            ->orderByDesc('issue_date')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.resale-cert.vendor-show')
            ->layout('components.layouts.portal', ['title' => $this->vendor->legal_name]);
    }
}
