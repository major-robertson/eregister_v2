<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackSignupAttribution
{
    /**
     * UTM parameters to track.
     *
     * @var array<string>
     */
    protected array $utmParams = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ];

    /**
     * Handle an incoming request.
     *
     * Captures UTM parameters and external referrer on first visit
     * to preserve first-touch attribution for signup tracking.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if user is already authenticated
        if ($request->user()) {
            return $next($request);
        }

        $this->captureUtmParams($request);
        $this->captureReferrer($request);
        $this->captureRedditClickId($request);
        $this->captureOpenAiClickId($request);

        return $next($request);
    }

    /**
     * Capture the Reddit Ads click id (?rdt_cid=) if not already set.
     * Passed to the Conversions API as a deterministic attribution signal.
     */
    protected function captureRedditClickId(Request $request): void
    {
        if (! session()->has('signup_rdt_cid') && $this->isStorableParam($request->query('rdt_cid'))) {
            session()->put('signup_rdt_cid', $request->query('rdt_cid'));
        }
    }

    /**
     * Capture the OpenAI/ChatGPT Ads click token (?oppref=) if not already
     * set. Passed to the Conversions API as an attribution signal for the
     * webhook-driven purchase events the pixel can't fire.
     */
    protected function captureOpenAiClickId(Request $request): void
    {
        if (! session()->has('signup_oppref') && $this->isStorableParam($request->query('oppref'))) {
            session()->put('signup_oppref', $request->query('oppref'));
        }
    }

    /**
     * Capture UTM parameters from the request if not already set.
     */
    protected function captureUtmParams(Request $request): void
    {
        foreach ($this->utmParams as $param) {
            $sessionKey = 'signup_'.$param;

            // Only capture if not already set (first-touch attribution)
            if (! session()->has($sessionKey) && $this->isStorableParam($request->query($param))) {
                session()->put($sessionKey, $request->query($param));
            }
        }
    }

    /**
     * Reject array (?param[]=) and oversize query values: they would fail
     * the users-table insert at registration and, because the session key
     * is first-touch and only cleared on success, 500 every registration
     * attempt for the rest of the session.
     */
    protected function isStorableParam(mixed $value): bool
    {
        return is_string($value) && $value !== '' && strlen($value) <= 255;
    }

    /**
     * Capture external referrer if not already set.
     */
    protected function captureReferrer(Request $request): void
    {
        // Only capture if not already set (first-touch attribution)
        if (session()->has('signup_referrer')) {
            return;
        }

        $referrer = $request->header('referer');

        if (! $referrer) {
            return;
        }

        // Only store external referrers (not from our own domain)
        $referrerHost = parse_url($referrer, PHP_URL_HOST);
        $currentHost = $request->getHost();

        if ($referrerHost && $referrerHost !== $currentHost) {
            session()->put('signup_referrer', $referrer);
        }
    }
}
