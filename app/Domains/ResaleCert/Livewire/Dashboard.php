<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\ResaleCert\Livewire\Concerns\ResolvesResaleContext;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Domains\ResaleCert\ResaleFollowUp;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Resale certificate workspace dashboard. Deliberately outside the
 * subscription gate: it renders subscribe / onboarding prompts for
 * businesses that haven't finished setup.
 */
class Dashboard extends Component
{
    use ResolvesResaleContext;

    public function mount(): void
    {
        if (! $this->resolveBusiness()) {
            return;
        }

        // People who came for certificates and haven't subscribed get the
        // follow-up emails. Rescued: email bookkeeping never breaks the page.
        rescue(fn () => ResaleFollowUp::onDashboard(Auth::user(), $this->business));
    }

    #[Computed]
    public function isSubscribed(): bool
    {
        return $this->business->subscribed(config('resale_cert.subscription_type'));
    }

    #[Computed]
    public function subscription(): ?\Laravel\Cashier\Subscription
    {
        return $this->business->subscription(config('resale_cert.subscription_type'));
    }

    #[Computed]
    public function profileComplete(): bool
    {
        return (bool) $this->business->resaleProfile?->isComplete();
    }

    /**
     * @return array{vendors: int, certificates: int, expiring: int}
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'vendors' => ResaleVendor::query()->count(),
            'certificates' => ResaleCertificate::query()->count(),
            'expiring' => ResaleCertificate::query()
                ->whereNotNull('expiration_date')
                ->whereBetween('expiration_date', [now(), now()->addDays(90)])
                ->count(),
        ];
    }

    /**
     * @return Collection<int, ResaleCertificate>
     */
    #[Computed]
    public function recentCertificates(): Collection
    {
        return ResaleCertificate::query()
            ->with(['vendor', 'stateRule'])
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, ResaleCertificate>
     */
    #[Computed]
    public function expiringCertificates(): Collection
    {
        return ResaleCertificate::query()
            ->with(['vendor', 'stateRule'])
            ->whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [now(), now()->addDays(90)])
            ->orderBy('expiration_date')
            ->limit(5)
            ->get();
    }

    /**
     * The state whose sample certificate the pricing pitch links to: the
     * business's own state when we have a form for it, otherwise Texas.
     */
    #[\Livewire\Attributes\Computed]
    public function sampleState(): string
    {
        $state = strtoupper((string) (($this->business->business_address ?? [])['state'] ?? ''));

        return $state !== '' && config()->has("resale_cert.states.{$state}") ? $state : 'TX';
    }

    public function render(): View
    {
        return view('livewire.resale-cert.dashboard')
            ->layout('components.layouts.portal', ['title' => 'Resale Certificates']);
    }
}
