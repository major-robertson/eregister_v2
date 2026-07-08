<?php

namespace App\Domains\Lien\Http\Controllers;

use App\Domains\Lien\Waivers\Services\WaiverPaymentService;
use App\Enums\PaymentStatus;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Stripe\StripeClient;

class WaiverPaymentController
{
    public function __construct(protected WaiverPaymentService $paymentService) {}

    /**
     * Post-payment landing for the lien waiver subscription. Renders success
     * when the webhook already activated the subscription, otherwise checks
     * Stripe directly (server-side fallback) before falling back to a
     * processing page. No JSON polling endpoint is registered for waivers,
     * so the processing page re-requests this URL (with an attempt counter)
     * until one of the checks above lands.
     */
    public function confirmation(Request $request): View
    {
        $business = Auth::user()->currentBusiness();

        abort_unless($business, 403);

        $paymentIntentId = $request->query('payment_intent');

        // Scope the no-intent fallback to lien-waiver subscription payments only.
        // Without this, a succeeded resale-cert (or any other business-morph
        // subscription) payment would satisfy the success check below and render
        // the waiver success page for a business that never bought waivers.
        $waiverPriceIds = Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'lien_waiver')
            ->where('billing_type', 'subscription')
            ->pluck('id');

        $payment = $paymentIntentId
            ? $business->payments()->where('stripe_payment_intent_id', $paymentIntentId)->first()
            : $business->payments()
                ->where('purchasable_type', $business->getMorphClass())
                ->where('billing_type', 'subscription')
                ->whereIn('price_id', $waiverPriceIds)
                ->latest()
                ->first();

        // Only fire ad-platform purchase conversions on the post-payment
        // landing (Stripe appends ?payment_intent=), not when the user later
        // revisits the page - otherwise the conversion double-counts.
        $trackConversion = $request->filled('payment_intent');

        $subscribed = $business->subscribed(config('lien_waivers.subscription_type'));

        if ($subscribed || $payment?->status === PaymentStatus::Succeeded) {
            return view('lien.waiver-payment-success', compact('business', 'payment', 'trackConversion'));
        }

        // Server-side fallback: ask Stripe directly in case the webhook is behind.
        if ($payment?->stripe_payment_intent_id && filled(config('cashier.secret'))) {
            $stripe = new StripeClient(config('cashier.secret'));
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                $this->paymentService->markSucceeded($payment, $pi);

                return view('lien.waiver-payment-success', compact('business', 'payment', 'trackConversion'));
            }
        }

        $attempts = max(0, (int) $request->query('attempts', 0));

        return view('lien.waiver-payment-processing', [
            'business' => $business,
            'payment' => $payment,
            'attempts' => $attempts,
            // Keep payment_intent on the retry URL so the success page still
            // fires its one-time ad conversions after the reload loop.
            'retryUrl' => route('lien.waivers.payment-confirmation', array_filter([
                'payment_intent' => $paymentIntentId,
                'attempts' => $attempts + 1,
            ])),
        ]);
    }
}
