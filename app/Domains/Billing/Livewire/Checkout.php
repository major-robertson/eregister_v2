<?php

namespace App\Domains\Billing\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\FormTypeConfig;
use App\Domains\Forms\Models\FormApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Checkout extends Component
{
    public Business $business;

    public FormApplication $application;

    public function mount(FormApplication $application): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        Gate::authorize('billing', $business);

        if ($application->business_id !== $business->id) {
            abort(403);
        }

        $this->business = $business;
        $this->application = $application;
    }

    public function checkout(): void
    {
        $config = FormTypeConfig::get($this->application->form_type);

        // Store definition snapshot before checkout
        $this->application->update([
            'definition_snapshot' => $this->buildDefinitionSnapshot(),
        ]);

        // Check if Stripe is configured
        if (empty($config['stripe_price_id'])) {
            // Stub mode: mark as paid immediately (for development)
            $this->stubCheckout();

            return;
        }

        // Real Stripe checkout
        if ($config['billing_type'] === 'subscription') {
            $this->handleSubscriptionCheckout($config);
        } else {
            $this->handleOneTimeCheckout($config);
        }
    }

    protected function handleSubscriptionCheckout(array $config): void
    {
        $session = $this->business
            ->newSubscription($config['subscription_name'], $config['stripe_price_id'])
            ->checkout([
                'success_url' => route('forms.application', $this->application).'?checkout=success',
                'cancel_url' => route('portal.checkout', $this->application),
                'metadata' => [
                    'application_id' => $this->application->id,
                    'form_type' => $this->application->form_type,
                ],
            ]);

        $this->application->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        $this->redirect($session->url);
    }

    protected function handleOneTimeCheckout(array $config): void
    {
        $quantity = $this->application->stateCount();

        $session = $this->business->checkout([
            $config['stripe_price_id'] => $quantity,
        ], [
            'success_url' => route('forms.application', $this->application).'?checkout=success',
            'cancel_url' => route('portal.checkout', $this->application),
            'metadata' => [
                'application_id' => $this->application->id,
                'form_type' => $this->application->form_type,
            ],
        ]);

        $this->application->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        $this->redirect($session->url);
    }

    protected function stubCheckout(): void
    {
        $config = FormTypeConfig::get($this->application->form_type);

        // Development stub: mark as paid immediately
        $this->application->update([
            'paid_at' => now(),
        ]);

        // For subscription billing, create a stub subscription record
        if ($config['billing_type'] === 'subscription') {
            $subscriptionName = $config['subscription_name'];

            // Only create if not already subscribed
            if (! $this->business->subscribed($subscriptionName)) {
                $this->business->subscriptions()->create([
                    'type' => $subscriptionName,
                    'stripe_id' => 'stub_'.uniqid(),
                    'stripe_status' => 'active',
                    'stripe_price' => $config['stripe_price_id'] ?? 'stub_price',
                    'quantity' => 1,
                ]);
            }
        }

        $this->redirect(route('forms.application', $this->application));
    }

    private function buildDefinitionSnapshot(): array
    {
        $registry = app(FormRegistry::class);
        $snapshots = ['base' => $registry->getBase($this->application->form_type)];

        foreach ($this->application->selected_states as $stateCode) {
            $snapshots['states'][$stateCode] = $registry->get($this->application->form_type, $stateCode);
        }

        return $snapshots;
    }

    public function render(): View
    {
        $config = FormTypeConfig::get($this->application->form_type);

        return view('livewire.billing.checkout', [
            'stateCount' => $this->application->stateCount(),
            'selectedStates' => $this->application->selected_states,
            'formType' => $this->application->form_type,
            'formTypeName' => $config['name'],
            'billingType' => $config['billing_type'],
            'isSubscription' => $config['billing_type'] === 'subscription',
            'subscriptionInterval' => $config['subscription_interval'] ?? null,
        ])->layout('layouts.app', ['title' => 'Checkout']);
    }
}
