<?php

namespace App\Domains\SalesTax\Http\Controllers;

use App\Domains\Forms\Models\FormApplication;
use App\Domains\SalesTax\Services\RegistrationPaymentService;
use App\Domains\SalesTax\Services\ResaleCertAddOn;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Stripe\StripeClient;

class RegistrationPaymentController
{
    public function __construct(
        protected RegistrationPaymentService $paymentService,
        protected ResaleCertAddOn $resaleCertAddOn,
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
            return $this->success($application, $payment, $trackConversion);
        }

        // Server-side fallback: ask Stripe directly.
        if ($payment?->stripe_payment_intent_id && filled(config('cashier.secret'))) {
            $stripe = new StripeClient(config('cashier.secret'));
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                $this->paymentService->markSucceeded($payment, $pi);

                return $this->success($application, $payment->fresh(), $trackConversion);
            }
        }

        // The application is already paid (e.g. via the dev stub or a legacy
        // checkout) even if no succeeded Stripe payment is retrievable here -
        // still show the receipt rather than polling forever.
        if ($application->isPaid()) {
            return $this->success($application, $payment, $trackConversion);
        }

        return view('sales-tax.registration.payment-processing', compact('application'));
    }

    /**
     * The receipt page. An order with the resale certificate add-on starts
     * it here too when the webhook has not yet (it runs once either way),
     * so the page can say whether the certificates are active.
     */
    private function success(FormApplication $application, ?Payment $payment, bool $trackConversion): View
    {
        $resaleCerts = $payment ? $this->resaleCertAddOn->outcome($payment) : null;

        return view('sales-tax.registration.payment-success', compact('application', 'payment', 'trackConversion', 'resaleCerts'));
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
