<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment)
    {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Receipt — eRegister',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payment-receipt',
            with: [
                'userName' => $this->recipientName(),
                'itemDescription' => $this->itemDescription(),
                'lines' => $this->lines(),
                'amount' => $this->payment->formattedAmount(),
                'paidAt' => $this->payment->paid_at?->eastern()->format('F j, Y g:i A'),
                'paymentId' => $this->payment->id,
            ],
        );
    }

    /**
     * Receipt rows. A sales tax order records its breakdown on the payment
     * (states x per-state fee, optional rush) so the receipt can itemize it;
     * every other payment is a single line.
     *
     * @return list<array{label: string, amount: string}>
     */
    protected function lines(): array
    {
        $meta = $this->payment->meta ?? [];

        if (! isset($meta['per_state_cents'], $meta['state_count'])) {
            return [['label' => $this->itemDescription(), 'amount' => $this->payment->formattedAmount()]];
        }

        $count = (int) $meta['state_count'];
        $perState = (int) $meta['per_state_cents'];

        $lines = [[
            'label' => $this->itemDescription().' ('.$count.' '.\Illuminate\Support\Str::plural('state', $count).' x $'.number_format($perState / 100, 2).')',
            'amount' => '$'.number_format($perState * $count / 100, 2),
        ]];

        if (! empty($meta['rush']) && ! empty($meta['rush_cents'])) {
            $lines[] = [
                'label' => 'Rush processing (filed within 2 business days)',
                'amount' => '$'.number_format(((int) $meta['rush_cents']) / 100, 2),
            ];
        }

        return $lines;
    }

    protected function recipientName(): string
    {
        $user = $this->payment->business->users()->first();

        return $user?->first_name ?? 'there';
    }

    protected function itemDescription(): string
    {
        $price = $this->payment->price;

        if ($price) {
            $family = match ($price->product_family) {
                'lien' => 'Lien Filing',
                'llc' => 'LLC Formation',
                'tax' => 'Sales & Use Tax Registration',
                'saas' => 'Subscription',
                default => ucfirst($price->product_family),
            };

            $product = match ($price->product_key) {
                'prelim_notice' => 'Preliminary Notice',
                'noi' => 'Notice of Intent',
                'mechanics_lien' => 'Mechanics Lien',
                'lien_release' => 'Lien Release',
                'demand_letter' => 'Payment Demand Letter',
                'sales_tax_permit' => 'Sales & Use Tax Permit',
                'llc' => 'LLC Formation',
                'resale_cert_generator' => 'Resale Certificate Generator (Annual Subscription)',
                default => ucwords(str_replace('_', ' ', $price->product_key)),
            };

            // Match on suffix so state-specific variants (e.g. "NJ_full_service")
            // still resolve to the right service-level label.
            $variantKey = (string) $price->variant_key;
            $variant = match (true) {
                str_ends_with($variantKey, 'full_service') => '(Full Service)',
                str_ends_with($variantKey, 'self_serve') => '(Self Serve)',
                default => '',
            };

            return trim("{$product} {$variant}");
        }

        $purchasable = $this->payment->purchasable;

        return class_basename($purchasable) ?? 'Service';
    }
}
