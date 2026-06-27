<?php

namespace App\Domains\Formations\Http\Controllers;

use App\Domains\Formations\Services\FormationPaymentService;
use App\Domains\Forms\Models\FormApplication;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Stripe\StripeClient;

class FormationPaymentController
{
    public function __construct(
        protected FormationPaymentService $paymentService
    ) {}

    /**
     * Payment confirmation page after the embedded Payment Element returns.
     * Renders success when the webhook already marked the payment paid,
     * otherwise checks the PaymentIntent directly (server-side fallback) before
     * falling back to a polling "processing" page.
     */
    public function confirmation(FormApplication $application, Request $request): View
    {
        Gate::authorize('view', $application);

        $paymentIntentId = $request->query('payment_intent');

        $payment = $paymentIntentId
            ? $application->payments()->where('stripe_payment_intent_id', $paymentIntentId)->first()
            : $application->payments()->latest()->first();

        // Webhook (or stub) already processed.
        if ($payment?->status === PaymentStatus::Succeeded) {
            return view('formations.payment-success', compact('application', 'payment'));
        }

        // Server-side fallback: ask Stripe directly about the PaymentIntent.
        if ($payment?->stripe_payment_intent_id && filled(config('cashier.secret'))) {
            $stripe = new StripeClient(config('cashier.secret'));
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                $this->paymentService->markSucceeded($payment, $pi);

                return view('formations.payment-success', compact('application', 'payment'));
            }
        }

        // Already paid via the dev stub or a legacy path even if no succeeded
        // Stripe payment is retrievable here - still show the receipt.
        if ($application->isPaid()) {
            $payment = $application->payments()->latest()->first();

            return view('formations.payment-success', compact('application', 'payment'));
        }

        return view('formations.payment-processing', compact('application'));
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
