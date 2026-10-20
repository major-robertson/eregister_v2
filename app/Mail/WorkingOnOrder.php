<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToSupport;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkingOnOrder extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToSupport, SerializesModels;

    public function __construct(public Payment $payment)
    {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->supportReplyTo(),
            subject: 'Working on Your Order — eRegister',
        );
    }

    public function content(): Content
    {
        $user = $this->payment->business->users()->first();

        return new Content(
            markdown: 'mail.working-on-order',
            with: [
                'userName' => $user?->first_name ?? 'there',
            ],
        );
    }
}
