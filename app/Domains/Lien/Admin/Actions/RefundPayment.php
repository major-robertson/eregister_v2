<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stripe\Refund;
use Stripe\Stripe;

class RefundPayment
{
    /**
     * @throws InvalidArgumentException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function execute(Payment $payment, User $refundedBy): void
    {
        if (! $payment->isRefundable()) {
            throw new InvalidArgumentException('This payment cannot be refunded.');
        }

        Stripe::setApiKey(config('cashier.secret'));

        $stripeRefund = Refund::create([
            'payment_intent' => $payment->stripe_payment_intent_id,
        ]);

        DB::transaction(function () use ($payment, $refundedBy, $stripeRefund) {
            $payment->update([
                'status' => PaymentStatus::Refunded,
                'stripe_refund_id' => $stripeRefund->id,
                'refunded_at' => now(),
                'refunded_by' => $refundedBy->id,
            ]);

            $filing = $payment->purchasable;

            if ($filing && method_exists($filing, 'events')) {
                $filing->events()->create([
                    'business_id' => $filing->business_id,
                    'event_type' => 'payment_refunded',
                    'payload_json' => [
                        'payment_id' => $payment->id,
                        'amount' => $payment->formattedAmount(),
                        'stripe_refund_id' => $stripeRefund->id,
                    ],
                    'created_by' => $refundedBy->id,
                ]);
            }
        });
    }
}
