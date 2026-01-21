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

        return $next($request);
    }

    /**
     * Capture UTM parameters from the request if not already set.
     */
    protected function captureUtmParams(Request $request): void
    {
        foreach ($this->utmParams as $param) {
            $sessionKey = 'signup_'.$param;

            // Only capture if not already set (first-touch attribution)
            if (! session()->has($sessionKey) && $request->has($param)) {
                session()->put($sessionKey, $request->input($param));
            }
        }
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
