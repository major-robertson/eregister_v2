<?php

namespace App\Services;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Records refunds made in Stripe (the dashboard or the admin Refund button)
 * on our payment rows, for every product. Fed by the charge.refunded webhook
 * and by payments:sync-refunds.
 *
 * A full refund marks the payment Refunded, which takes it out of every
 * revenue figure, and a lien filing it paid for becomes Refunded too (which
 * also stops the job's deadline reminders). A partial refund only records
 * the refunded amount in the payment's meta.
 */
class StripeRefundRecorder
{
    public const FULL = 'refunded';

    public const PARTIAL = 'partial';

    public const ALREADY = 'already_refunded';

    /**
     * Record the refund a charge.refunded event reports. Null when the
     * charge isn't one of ours: the old TaxResaleCertificate app shares this
     * Stripe account.
     */
    public function fromCharge(object $charge): ?string
    {
        $payment = $this->findPayment($charge->payment_intent ?? null, $charge->id ?? null);

        if ($payment === null) {
            return null;
        }

        // Newer API versions leave the refunds list off the charge.
        $latestRefund = $charge->refunds->data[0] ?? null;

        return $this->record(
            $payment,
            (int) ($charge->amount_refunded ?? 0),
            $latestRefund->id ?? null,
            isset($latestRefund->created) ? Carbon::createFromTimestamp($latestRefund->created) : null,
        );
    }

    /**
     * Record every succeeded refund in a Stripe refunds list, summed per
     * charge. With $dryRun nothing is saved; each row says what would happen.
     *
     * @param  iterable<object>  $refunds
     * @return array{rows: list<array{payment: int, amount: float, refunded: float, outcome: string}>, not_ours: int}
     */
    public function sync(iterable $refunds, bool $dryRun = false): array
    {
        $byCharge = [];

        foreach ($refunds as $refund) {
            if (($refund->status ?? null) !== 'succeeded') {
                continue;
            }

            $key = $refund->payment_intent ?? $refund->charge ?? null;

            if ($key === null) {
                continue;
            }

            $byCharge[$key] ??= ['payment_intent' => $refund->payment_intent ?? null, 'charge' => $refund->charge ?? null, 'cents' => 0, 'latest' => null];
            $byCharge[$key]['cents'] += (int) $refund->amount;

            if ($byCharge[$key]['latest'] === null || $refund->created > $byCharge[$key]['latest']->created) {
                $byCharge[$key]['latest'] = $refund;
            }
        }

        $rows = [];
        $notOurs = 0;

        foreach ($byCharge as $group) {
            $payment = $this->findPayment($group['payment_intent'], $group['charge']);

            if ($payment === null) {
                $notOurs++;

                continue;
            }

            $outcome = $dryRun
                ? $this->wouldRecord($payment, $group['cents'])
                : $this->record($payment, $group['cents'], $group['latest']->id, Carbon::createFromTimestamp($group['latest']->created));

            $rows[] = [
                'payment' => $payment->id,
                'amount' => $payment->amount_cents / 100,
                'refunded' => $group['cents'] / 100,
                'outcome' => $outcome,
            ];
        }

        return ['rows' => $rows, 'not_ours' => $notOurs];
    }

    public function findPayment(?string $paymentIntentId, ?string $chargeId): ?Payment
    {
        if ($paymentIntentId) {
            $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

            if ($payment) {
                return $payment;
            }
        }

        return $chargeId ? Payment::where('stripe_charge_id', $chargeId)->first() : null;
    }

    /**
     * @return string|null One of the constants; null when nothing was refunded.
     */
    public function record(Payment $payment, int $refundedCents, ?string $refundId = null, ?CarbonInterface $refundedAt = null): ?string
    {
        if ($refundedCents <= 0) {
            return null;
        }

        return DB::transaction(function () use ($payment, $refundedCents, $refundId, $refundedAt): string {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            // The admin Refund button or an earlier event already recorded it.
            if ($payment->status === PaymentStatus::Refunded) {
                return self::ALREADY;
            }

            if ($refundedCents < $payment->amount_cents) {
                $payment->update([
                    'meta' => array_merge($payment->meta ?? [], ['refunded_cents' => $refundedCents]),
                    'stripe_refund_id' => $refundId ?? $payment->stripe_refund_id,
                ]);

                return self::PARTIAL;
            }

            $payment->update([
                'status' => PaymentStatus::Refunded,
                'stripe_refund_id' => $refundId ?? $payment->stripe_refund_id,
                'refunded_at' => $refundedAt ?? now(),
            ]);

            $filing = $payment->purchasable;

            if ($filing instanceof LienFiling && ! in_array($filing->status, [FilingStatus::Refunded, FilingStatus::Canceled], true)) {
                $filing->update(['status' => FilingStatus::Refunded]);

                $filing->events()->create([
                    'business_id' => $filing->business_id,
                    'event_type' => 'payment_refunded',
                    'payload_json' => [
                        'payment_id' => $payment->id,
                        'amount' => $payment->formattedAmount(),
                        'stripe_refund_id' => $refundId,
                        'source' => 'stripe',
                    ],
                    'created_by' => null,
                ]);
            }

            Log::info('Stripe refund recorded', ['payment_id' => $payment->id, 'refunded_cents' => $refundedCents]);

            return self::FULL;
        });
    }

    private function wouldRecord(Payment $payment, int $refundedCents): string
    {
        return match (true) {
            $payment->status === PaymentStatus::Refunded => self::ALREADY,
            $refundedCents < $payment->amount_cents => self::PARTIAL,
            default => self::FULL,
        };
    }
}
