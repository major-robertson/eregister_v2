<?php

namespace App\Domains\Lien\Services;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFulfillmentTask;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;

class LienPaymentService
{
    /**
     * Mark a payment as succeeded and transition the filing status.
     * This is idempotent - safe to call multiple times.
     */
    public function markSucceeded(Payment $payment, PaymentIntent $stripePaymentIntent): void
    {
        DB::transaction(function () use ($payment, $stripePaymentIntent) {
            $payment = Payment::lockForUpdate()->find($payment->id);

            if ($payment->status === PaymentStatus::Succeeded) {
                return; // Already done
            }

            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'stripe_charge_id' => $stripePaymentIntent->latest_charge,
                'paid_at' => now(),
            ]);

            $filing = $payment->purchasable;

            if ($filing->status === FilingStatus::AwaitingPayment) {
                $filing->transitionTo(FilingStatus::Paid);
            }

            if ($filing->isFullService() && $filing->status === FilingStatus::Paid) {
                $filing->transitionTo(FilingStatus::InFulfillment);
                LienFulfillmentTask::firstOrCreate(
                    ['filing_id' => $filing->id],
                    ['business_id' => $filing->business_id, 'status' => 'queued']
                );
            }

            // Mark deadline as completed
            if ($filing->projectDeadline && $filing->projectDeadline->status->value === 'pending') {
                $filing->projectDeadline->update([
                    'status' => 'completed',
                    'completed_filing_id' => $filing->id,
                ]);
            }
        });
    }
}
