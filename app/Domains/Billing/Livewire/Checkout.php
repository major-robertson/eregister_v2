<?php

namespace App\Domains\Billing\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\FormTypeConfig;
use App\Domains\Forms\Models\FormApplication;
use App\Models\EmailSequence;
use App\Support\Workspaces\WorkspaceRegistry;
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
                'success_url' => $this->checkoutSuccessUrl(),
                'cancel_url' => route('portal.checkout', $this->application),
                'metadata' => [
                    'application_id' => $this->application->id,
                    'form_type' => $this->application->form_type,
                ],
            ]);

        $this->application->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        EmailSequence::startFor(
            'abandon_checkout',
            $this->application,
            Auth::user(),
            $this->business,
            route('portal.checkout', $this->application)
        );

        $this->redirect($session->url);
    }

    protected function handleOneTimeCheckout(array $config): void
    {
        $quantity = $this->application->stateCount();

        $session = $this->business->checkout([
            $config['stripe_price_id'] => $quantity,
        ], [
            'success_url' => $this->checkoutSuccessUrl(),
            'cancel_url' => route('portal.checkout', $this->application),
            'metadata' => [
                'application_id' => $this->application->id,
                'form_type' => $this->application->form_type,
            ],
        ]);

        $this->application->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        EmailSequence::startFor(
            'abandon_checkout',
            $this->application,
            Auth::user(),
            $this->business,
            route('portal.checkout', $this->application)
        );

        $this->redirect($session->url);
    }

    /**
     * Build the Stripe `success_url` for both subscription and one-time
     * checkouts. Uses the workspace-aware route helper so users land back
     * on the correct workspace-prefixed application URL after payment;
     * falls back to the generic dashboard when no workspace claims the
     * form type (defensive — should not happen with current config).
     */
    protected function checkoutSuccessUrl(): string
    {
        $workspace = app(WorkspaceRegistry::class)
            ->findByFormType($this->application->form_type);

        return $workspace?->applicationRouteFor($this->application, ['checkout' => 'success'])
            ?? route('dashboard').'?checkout=success';
    }

    protected function stubCheckout(): void
    {
        $config = FormTypeConfig::get($this->application->form_type);

        $updates = ['paid_at' => now()];

        if (in_array($config['billing_type'], ['one_time_per_state', 'one_time'])) {
            $updates['status'] = 'submitted';
            $updates['submitted_at'] = now();
            $updates['locked_at'] = now();
        }

        $this->application->update($updates);

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

        session()->flash('success', 'Your application has been submitted successfully.');

        $this->redirect(route('dashboard'));
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
