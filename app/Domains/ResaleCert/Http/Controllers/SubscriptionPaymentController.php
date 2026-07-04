<?php

namespace App\Domains\ResaleCert\Http\Controllers;

use App\Domains\ResaleCert\Services\ResaleCertPaymentService;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Stripe\StripeClient;

class SubscriptionPaymentController
{
    public function __construct(protected ResaleCertPaymentService $paymentService) {}

    /**
     * Post-payment landing. Renders success when the webhook already
     * activated the subscription, otherwise checks Stripe directly
     * (server-side fallback) before falling back to a polling page.
     */
    public function confirmation(Request $request): View
    {
        $business = Auth::user()->currentBusiness();

        abort_unless($business, 403);

        $paymentIntentId = $request->query('payment_intent');

        $payment = $paymentIntentId
            ? $business->payments()->where('stripe_payment_intent_id', $paymentIntentId)->first()
            : $business->payments()
                ->where('purchasable_type', $business->getMorphClass())
                ->where('billing_type', 'subscription')
                ->latest()
                ->first();

        // Only fire ad-platform purchase conversions on the post-payment
        // landing (Stripe appends ?payment_intent=), not when the user later
        // revisits the page - otherwise the conversion double-counts.
        $trackConversion = $request->filled('payment_intent');

        $subscribed = $business->subscribed(config('resale_cert.subscription_type'));

        if ($subscribed || $payment?->status === PaymentStatus::Succeeded) {
            return view('resale-cert.payment-success', compact('business', 'payment', 'trackConversion'));
        }

        // Server-side fallback: ask Stripe directly in case the webhook is behind.
        if ($payment?->stripe_payment_intent_id && filled(config('cashier.secret'))) {
            $stripe = new StripeClient(config('cashier.secret'));
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                $this->paymentService->markSucceeded($payment, $pi);

                return view('resale-cert.payment-success', compact('business', 'payment', 'trackConversion'));
            }
        }

        return view('resale-cert.payment-processing', compact('business', 'payment'));
    }

    /**
     * Payment status polling endpoint for the processing page.
     *
     * @return array{status: string, subscribed: bool}
     */
    public function status(): array
    {
        $business = Auth::user()->currentBusiness();

        abort_unless($business, 403);

        $payment = $business->payments()
            ->where('purchasable_type', $business->getMorphClass())
            ->where('billing_type', 'subscription')
            ->latest()
            ->first();

        return [
            'status' => $payment?->status?->value ?? 'unknown',
            'subscribed' => $business->subscribed(config('resale_cert.subscription_type')),
        ];
    }
}
