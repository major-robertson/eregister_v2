<?php

namespace App\Domains\SalesTax\Http\Controllers;

use App\Domains\Forms\Models\FormApplication;
use App\Domains\SalesTax\Services\RegistrationPaymentService;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Stripe\StripeClient;

class RegistrationPaymentController
{
    public function __construct(
        protected RegistrationPaymentService $paymentService
    ) {}

    /**
     * Payment confirmation page after the Stripe redirect. Renders success
     * when the webhook already marked the payment paid, otherwise checks
     * Stripe directly (server-side fallback) before falling back to a
     * polling "processing" page.
     */
    public function confirmation(FormApplication $application, Request $request): View
    {
        Gate::authorize('view', $application);

        $paymentIntentId = $request->query('payment_intent');

        $payment = $paymentIntentId
            ? $application->payments()->where('stripe_payment_intent_id', $paymentIntentId)->first()
            : $application->payments()->latest()->first();

        // Only fire the Google Ads purchase conversion on the post-payment
        // landing (Stripe appends ?payment_intent=), not when the user later
        // clicks "View" on the receipt - otherwise the conversion double-counts.
        $trackConversion = $request->filled('payment_intent');

        // Webhook (or stub) already processed.
        if ($payment?->status === PaymentStatus::Succeeded) {
            return view('sales-tax.registration.payment-success', compact('application', 'payment', 'trackConversion'));
        }

        // Server-side fallback: ask Stripe directly.
        if ($payment?->stripe_payment_intent_id && filled(config('cashier.secret'))) {
            $stripe = new StripeClient(config('cashier.secret'));
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                $this->paymentService->markSucceeded($payment, $pi);

                return view('sales-tax.registration.payment-success', compact('application', 'payment', 'trackConversion'));
            }
        }

        // The application is already paid (e.g. via the dev stub or a legacy
        // checkout) even if no succeeded Stripe payment is retrievable here -
        // still show the receipt rather than polling forever.
        if ($application->isPaid()) {
            return view('sales-tax.registration.payment-success', compact('application', 'payment', 'trackConversion'));
        }

        return view('sales-tax.registration.payment-processing', compact('application'));
    }

    /**
     * Payment status polling endpoint.
     *
     * @return array{status: string, paid: bool}
     */
    public function status(FormApplication $application): array
    {
        Gate::authorize('view', $application);

        $payment = $application->payments()->latest()->first();

        return [
            'status' => $payment?->status?->value ?? 'unknown',
            'paid' => $application->paid_at !== null,
        ];
    }
}
