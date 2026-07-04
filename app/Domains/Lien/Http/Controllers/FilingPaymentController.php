<?php

namespace App\Domains\Lien\Http\Controllers;

use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Services\LienPaymentService;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\StripeClient;

class FilingPaymentController
{
    public function __construct(
        protected LienPaymentService $paymentService
    ) {}

    /**
     * Handle the payment confirmation page after Stripe redirect.
     */
    public function confirmation(LienFiling $filing, Request $request): View
    {
        // Prefer ?payment_intent= from Stripe redirect
        $paymentIntentId = $request->query('payment_intent');

        $payment = $paymentIntentId
            ? $filing->payments()->where('stripe_payment_intent_id', $paymentIntentId)->first()
            : $filing->payments()->latest()->first();

        // Only fire ad-platform purchase conversions on the post-payment
        // landing (Stripe appends ?payment_intent=), not when the user later
        // revisits the receipt - otherwise the conversion double-counts.
        $trackConversion = $request->filled('payment_intent');

        // Webhook already processed
        if ($payment?->status === PaymentStatus::Succeeded) {
            return view('lien.payment-success', compact('filing', 'payment', 'trackConversion'));
        }

        // Server-side fallback: check Stripe directly
        if ($payment?->stripe_payment_intent_id) {
            $stripe = new StripeClient(config('cashier.secret'));
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                // Update DB idempotently (webhook may still arrive)
                $this->paymentService->markSucceeded($payment, $pi);

                return view('lien.payment-success', compact('filing', 'payment', 'trackConversion'));
            }
        }

        // Show "processing" with polling
        return view('lien.payment-processing', compact('filing'));
    }

    /**
     * API endpoint for payment status polling.
     */
    public function status(LienFiling $filing): array
    {
        $payment = $filing->payments()->latest()->first();

        return [
            'status' => $payment?->status?->value ?? 'unknown',
            'paid' => $filing->paid_at !== null,
        ];
    }
}
