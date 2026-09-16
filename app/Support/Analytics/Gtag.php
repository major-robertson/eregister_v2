<?php

namespace App\Support\Analytics;

/**
 * GA4 funnel events.
 *
 * Events that belong to a page the user is about to be redirected to (the
 * landing-page starter sends guests to /register) are queued in the session
 * and drained by partials/head on the next full page render. Livewire
 * actions fire theirs directly through eventJs() and $this->js().
 */
class Gtag
{
    private const SESSION_KEY = 'gtag.queued';

    /**
     * @param  array<string, mixed>  $params
     */
    public static function queue(string $event, array $params = []): void
    {
        $queued = session()->get(self::SESSION_KEY, []);
        $queued[] = ['name' => $event, 'params' => $params];

        session()->put(self::SESSION_KEY, $queued);
    }

    /**
     * @return list<array{name: string, params: array<string, mixed>}>
     */
    public static function drain(): array
    {
        $queued = session()->pull(self::SESSION_KEY, []);

        return is_array($queued) ? array_values($queued) : [];
    }

    /**
     * Inline JS for a Livewire component's $this->js(): fires the event from
     * an action (a waiver was generated) without waiting for a page render.
     *
     * @param  array<string, mixed>  $params
     */
    public static function eventJs(string $event, array $params = []): string
    {
        return sprintf(
            "window.gtag && gtag('event', %s, %s);",
            json_encode($event, JSON_THROW_ON_ERROR),
            json_encode((object) $params, JSON_THROW_ON_ERROR),
        );
    }
}
