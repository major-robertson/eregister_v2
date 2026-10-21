<?php

namespace App\Mail\Concerns;

use Illuminate\Mail\Mailables\Address;

/**
 * For service mail a customer may answer (filing reminders, receipts, e-sign
 * requests). Pass supportReplyTo() as the envelope's replyTo so a reply
 * reaches the support inbox (mail.support_address), not the sending address.
 */
trait RepliesToSupport
{
    /**
     * @return list<Address>
     */
    protected function supportReplyTo(): array
    {
        return [new Address(config('mail.support_address'))];
    }
}
