<?php

namespace App\Services;

use App\Jobs\SendOpenAiConversion;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Server-side OpenAI (ChatGPT) Ads Conversions API client. Mirrors the
 * browser pixel's events with an identical event id (pixel event_id ==
 * CAPI id) so OpenAI deduplicates the two sources on pixelId + event
 * name + id. CAPI covers what the pixel misses: ad blockers, and
 * webhook-only payments where the buyer never returns to the success page.
 *
 * Match keys are SHA-256 hashed (lowercase 64-char hex) exactly as the
 * pixel's advanced-matching init hashes them, so both sources line up.
 * IP, user agent, and oppref are sent raw per the OpenAI spec.
 */
class OpenAiConversionsApi
{
    public static function enabled(): bool
    {
        return (bool) config('services.openai_ads.capi_enabled')
            && filled(config('services.openai_ads.capi_token'));
    }

    /**
     * Queue an order/subscription conversion for a payment that just
     * transitioned to succeeded. Only call from the first-transition
     * branch of a payment service - that guard is what makes it fire once.
     */
    public function queuePurchase(Payment $payment): void
    {
        if (! static::enabled()) {
            return;
        }

        $event = $this->purchaseEvent($payment);

        if ($event) {
            SendOpenAiConversion::dispatch($event);
        }
    }

    public function queueRegistration(User $user, Request $request): void
    {
        if (! static::enabled()) {
            return;
        }

        SendOpenAiConversion::dispatch($this->registrationEvent($user, $request));
    }

    /**
     * A paid subscription is a plan_enrollment (subscription_created); a
     * one-time charge is a contents order (order_created). The event id
     * must mirror the pixel's event_id on the matching success page.
     *
     * @return array<string, mixed>|null Null when the payment has no
     *                                   resolvable user (nothing to match on).
     */
    public function purchaseEvent(Payment $payment): ?array
    {
        $user = $payment->business?->users()->first();

        if (! $user) {
            return null;
        }

        $isSubscription = $payment->billing_type === 'subscription';

        return array_filter([
            'id' => ($isSubscription ? 'subscription-' : 'order-').$payment->id,
            'type' => $isSubscription ? 'subscription_created' : 'order_created',
            'timestamp_ms' => (int) ($payment->paid_at ?? now())->getPreciseTimestamp(3),
            'source_url' => config('app.url'),
            'action_source' => 'web',
            'oppref' => $user->signup_oppref,
            'user' => $this->matchKeys($user),
            'data' => array_filter([
                'type' => $isSubscription ? 'plan_enrollment' : 'contents',
                'amount' => round($payment->amount_cents / 100, 2),
                'currency' => 'USD',
            ], fn ($v) => $v !== null && $v !== ''),
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /**
     * Built at request time so the event captures the registrant's browser
     * context (ip, user agent, and the pixel's __oppref first-party cookie).
     *
     * @return array<string, mixed>
     */
    public function registrationEvent(User $user, Request $request): array
    {
        return array_filter([
            'id' => 'signup-'.$user->id,
            'type' => 'registration_completed',
            'timestamp_ms' => (int) now()->getPreciseTimestamp(3),
            'source_url' => $request->url(),
            'action_source' => 'web',
            // Prefer the cookie the pixel actually used this session; fall
            // back to the click token captured first-touch at signup.
            'oppref' => $request->cookie('__oppref') ?: $user->signup_oppref,
            'user' => array_filter([
                ...$this->matchKeys($user),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ], fn ($v) => filled($v)),
            'data' => ['type' => 'customer_action'],
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /**
     * Send events immediately. Request code should prefer the queued path
     * (queuePurchase / queueRegistration); this is for the job and testing.
     *
     * @param  list<array<string, mixed>>  $events
     */
    public function send(array $events, ?bool $validateOnly = null): Response
    {
        return Http::withToken(config('services.openai_ads.capi_token'))
            ->acceptJson()
            ->post('https://bzr.openai.com/v1/events?pid='.config('services.openai_ads.pixel_id'), [
                'validate_only' => $validateOnly ?? (bool) config('services.openai_ads.capi_validate_only'),
                'events' => $events,
            ])
            ->throw();
    }

    /**
     * @return array<string, string>
     */
    private function matchKeys(User $user): array
    {
        return array_filter([
            'email_sha256' => $this->hash(mb_strtolower(trim($user->email))),
            'external_id_sha256' => $this->hash((string) $user->id),
        ], fn ($v) => filled($v));
    }

    private function hash(?string $value): ?string
    {
        return filled($value) ? hash('sha256', $value) : null;
    }
}
