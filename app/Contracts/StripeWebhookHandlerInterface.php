<?php

namespace App\Contracts;

use Illuminate\Http\Response;
use Stripe\Event;

interface StripeWebhookHandlerInterface
{
    /**
     * Handle a Stripe webhook event.
     *
     * The handler receives the full Stripe\Event and decides what to do
     * based on $event->type. This allows handlers to support multiple
     * event types (payment_intent.*, invoice.*, customer.subscription.*, etc.)
     */
    public function handle(Event $event): Response;
}
