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
                'amount' => $this->payment->formattedAmount(),
                'paidAt' => $this->payment->paid_at?->format('F j, Y g:i A'),
                'paymentId' => $this->payment->id,
            ],
        );
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
                default => ucwords(str_replace('_', ' ', $price->product_key)),
            };

            $variant = match ($price->variant_key) {
                'full_service' => '(Full Service)',
                'self_serve' => '(Self Serve)',
                default => '',
            };

            return trim("{$product} {$variant}");
        }

        $purchasable = $this->payment->purchasable;

        return class_basename($purchasable) ?? 'Service';
    }
}
