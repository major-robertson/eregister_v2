<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class FilingCheckout extends Component
{
    public Business $business;

    public LienFiling $filing;

    public function mount(LienFiling $filing): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        Gate::authorize('checkout', $filing);

        if ($filing->business_id !== $business->id) {
            abort(403);
        }

        $this->business = $business;
        $this->filing = $filing;
    }

    public function checkout(): void
    {
        $slug = $this->filing->documentType->slug;
        $suffix = $this->filing->isFullService() ? '_full' : '_self';
        $priceId = config('lien.stripe_prices.'.$slug.$suffix);

        // If no price configured, use stub checkout for development
        if (empty($priceId)) {
            $this->stubCheckout();

            return;
        }

        // Real Stripe checkout
        $session = $this->business->checkout([$priceId => 1], [
            'success_url' => route('lien.filings.show', $this->filing).'?checkout=success',
            'cancel_url' => route('lien.filings.checkout', $this->filing),
            'metadata' => [
                'filing_id' => $this->filing->id,
                'filing_public_id' => $this->filing->public_id,
                'document_type' => $this->filing->documentType->slug,
            ],
        ]);

        $this->filing->update(['stripe_checkout_session_id' => $session->id]);

        $this->redirect($session->url);
    }

    protected function stubCheckout(): void
    {
        // Development stub: mark as paid immediately
        if ($this->filing->status === FilingStatus::AwaitingPayment) {
            $this->filing->transitionTo(FilingStatus::Paid);
        }

        // For full-service, transition to in_fulfillment
        if ($this->filing->isFullService() && $this->filing->status === FilingStatus::Paid) {
            $this->filing->transitionTo(FilingStatus::InFulfillment);

            // Create fulfillment task
            $this->filing->fulfillmentTask()->firstOrCreate([
                'business_id' => $this->filing->business_id,
            ], [
                'status' => 'queued',
            ]);
        }

        // Mark deadline as completed
        if ($this->filing->projectDeadline) {
            $this->filing->projectDeadline->update([
                'status' => 'completed',
                'completed_filing_id' => $this->filing->id,
            ]);
        }

        $this->redirect(route('lien.filings.show', $this->filing).'?checkout=success');
    }

    public function render(): View
    {
        $pricing = config('lien.pricing.'.$this->filing->documentType->slug, [
            'self_serve' => 4900,
            'full_service' => 9900,
        ]);

        $price = $this->filing->isFullService()
            ? $pricing['full_service']
            : $pricing['self_serve'];

        return view('livewire.lien.filing-checkout', [
            'price' => $price,
            'formattedPrice' => '$'.number_format($price / 100, 2),
        ])->layout('layouts.lien', ['title' => 'Checkout']);
    }
}
