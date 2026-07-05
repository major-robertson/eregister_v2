<?php

namespace App\Services;

use App\Jobs\SendRedditConversion;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Server-side Reddit Conversions API client. Mirrors the browser pixel's
 * events with identical conversion ids so Reddit deduplicates the two
 * sources; CAPI covers what the pixel misses (ad blockers, and webhook-only
 * payments where the buyer never returns to the success page).
 *
 * Identifiers are SHA-256 hashed with the same normalization the pixel
 * applies client-side, so match keys line up across both sources.
 */
class RedditConversionsApi
{
    public static function enabled(): bool
    {
        return (bool) config('services.reddit.capi_enabled')
            && filled(config('services.reddit.capi_token'));
    }

    /**
     * Queue a Purchase conversion for a payment that just transitioned to
     * succeeded. Only call from the first-transition branch of a payment
     * service - that guard is what makes the event fire exactly once.
     */
    public function queuePurchase(Payment $payment): void
    {
        if (! static::enabled()) {
            return;
        }

        $event = $this->purchaseEvent($payment);

        if ($event) {
            SendRedditConversion::dispatch($event);
        }
    }

    public function queueSignUp(User $user, Request $request): void
    {
        if (! static::enabled()) {
            return;
        }

        SendRedditConversion::dispatch($this->signUpEvent($user, $request));
    }

    /**
     * @return array<string, mixed>|null Null when the payment has no
     *                                   resolvable user (nothing to match on).
     */
    public function purchaseEvent(Payment $payment): ?array
    {
        $user = $payment->business?->users()->first();

        if (! $user) {
            return null;
        }

        return array_filter([
            'event_at' => ($payment->paid_at ?? now())->toIso8601String(),
            'event_type' => ['tracking_type' => 'Purchase'],
            'click_id' => $user->signup_rdt_cid,
            'user' => $this->matchKeys($user),
            'event_metadata' => [
                'currency' => 'USD',
                'value_decimal' => round($payment->amount_cents / 100, 2),
                'item_count' => 1,
                // Must mirror the pixel's conversionId for deduplication.
                'conversion_id' => 'purchase-'.$payment->id,
            ],
        ]);
    }

    /**
     * Built at request time so the event captures the registrant's browser
     * context (ip, user agent, and the pixel's _rdt_uuid first-party cookie).
     *
     * @return array<string, mixed>
     */
    public function signUpEvent(User $user, Request $request): array
    {
        return array_filter([
            'event_at' => now()->toIso8601String(),
            'event_type' => ['tracking_type' => 'SignUp'],
            'click_id' => $user->signup_rdt_cid,
            'user' => array_filter([
                ...$this->matchKeys($user),
                'ip_address' => $this->hash($request->ip()),
                'user_agent' => $request->userAgent(),
                'uuid' => $request->cookie('_rdt_uuid'),
            ]),
            'event_metadata' => [
                // Must mirror the pixel's conversionId for deduplication.
                'conversion_id' => 'signup-'.$user->id,
            ],
        ]);
    }

    /**
     * Send events immediately. Request code should prefer the queued path
     * (queuePurchase / queueSignUp); this is for the job and manual testing.
     *
     * @param  list<array<string, mixed>>  $events
     */
    public function send(array $events, ?string $testId = null): Response
    {
        return Http::withToken(config('services.reddit.capi_token'))
            ->acceptJson()
            ->post(
                'https://ads-api.reddit.com/api/v2.0/conversions/events/'.config('services.reddit.pixel_id'),
                array_filter([
                    'test_id' => $testId ?? config('services.reddit.capi_test_id'),
                    'events' => $events,
                ])
            )
            ->throw();
    }

    /**
     * @return array<string, string>
     */
    private function matchKeys(User $user): array
    {
        return array_filter([
            'email' => $this->hash(mb_strtolower(trim($user->email))),
            'external_id' => $this->hash((string) $user->id),
        ]);
    }

    private function hash(?string $value): ?string
    {
        return filled($value) ? hash('sha256', $value) : null;
    }
}
