<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

@php
    /*
     * Reddit and OpenAI ad pixels load only where a paid visit or a conversion
     * happens: campaign landing pages, sign-up, onboarding (the sign-up
     * conversion fires there), checkout and payment confirmation (the purchase
     * conversions). Every other page (home, product, state and government
     * pages) gets the Google tag alone. Route name patterns, as routeIs().
     */
    $adPixelRoutes = [
        'lp.*',                   // /lp/* paid-search landing pages
        'marketing.landing.*',    // /go/* direct-mail landing pages
        'landing2',
        'register',
        'portal.select-business', // waiver sign-ups: the one-screen setup fires the sign-up conversion
        '*.onboarding',           // portal, lien and resale onboarding
        '*.checkout',
        '*.payment-confirmation',
    ];

    // No ad tags on admin pages or the third-party sales demos (simulated
    // MDCPS / Florida EOG sites shown to procurement evaluators).
    $adTagsOn = app()->environment('production')
        && ! request()->routeIs('admin.*', 'mdcps-demo.*', 'government.florida-eog-demo-*');
    $adPixelsOn = $adTagsOn && request()->routeIs(...$adPixelRoutes);
@endphp

{{-- Ad and analytics tags. They load in production only: Google's tag
     diagnostics showed the production tag firing from the local dev domain, so
     local browsing was landing in Analytics and a local test purchase could
     have recorded a real Google Ads conversion. Pages still call gtag(), rdt()
     and oaiq() for their events, so everywhere else they exist as no-ops.

     In production only small inline stubs run here, and they queue every
     call. The tag scripts themselves (gtag.js, the Reddit and OpenAI pixels)
     are fetched by eregLoadScript() after the window load event, once the
     browser is idle, so they never compete with the page for the network or
     the main thread. Each script sends its queued calls when it arrives.
     These inline scripts sit above the stylesheets so they do not wait for
     the CSS to download. --}}
@unless(app()->environment('production'))
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){}
  window.rdt = window.rdt || function(){};
  window.oaiq = window.oaiq || function(){};
@foreach (\App\Support\Analytics\Gtag::drain() as $queuedEvent)
  gtag('event', @js($queuedEvent['name']), @js((object) $queuedEvent['params']));
@endforeach
</script>
@endunless

@if($adTagsOn)
<script>
  // Google tag (gtag.js): GA4 and Google Ads share one script load.
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-MSVBK7VE6P');
  gtag('config', 'AW-984288380');

  // Reddit and OpenAI stubs, in each vendor's own queue shape. Their scripts
  // load only on the ad pixel routes; elsewhere the calls queue and go nowhere.
  if (!window.rdt) {
    var rdt = window.rdt = function () { rdt.sendEvent ? rdt.sendEvent.apply(rdt, arguments) : rdt.callQueue.push(arguments); };
    rdt.callQueue = [];
  }
  if (!window.oaiq) {
    var oaiq = window.oaiq = function () { oaiq.q.push(arguments); };
    oaiq.q = [];
  }

  // Deferred loader: after window load, when the browser is idle (at most 2 s
  // later), or 2 s after load where requestIdleCallback is missing (Safari).
  if (!window.eregLoadScript) {
    (function (w, d) {
      var ready = false, pending = [], requested = {};
      function inject(src) {
        var s = d.createElement('script');
        s.async = true;
        s.src = src;
        d.head.appendChild(s);
      }
      function flush() {
        ready = true;
        pending.splice(0).forEach(inject);
      }
      function whenIdle() {
        if (w.requestIdleCallback) { w.requestIdleCallback(flush, { timeout: 2000 }); } else { setTimeout(flush, 2000); }
      }
      if (d.readyState === 'complete') { whenIdle(); } else { window.addEventListener('load', whenIdle); }
      w.eregLoadScript = function (src) {
        if (requested[src]) return;
        requested[src] = true;
        ready ? inject(src) : pending.push(src);
      };
    })(window, document);
  }

  eregLoadScript('https://www.googletagmanager.com/gtag/js?id=G-MSVBK7VE6P');
</script>
@php($queuedEvents = \App\Support\Analytics\Gtag::drain())
@if($queuedEvents)
<script>
@foreach ($queuedEvents as $queuedEvent)
  gtag('event', @js($queuedEvent['name']), @js((object) $queuedEvent['params']));
@endforeach
</script>
@endif
@endif

@if($adPixelsOn)
<!-- Reddit Pixel and OpenAI (ChatGPT) Ads Pixel: the stubs and the deferred
     script load are in the Google tag block above. -->
<script>
@auth
rdt('init', 'a2_j93ntx48v4gy', {
    email: @js(auth()->user()->email),
    externalId: @js((string) auth()->id()),
});
@else
rdt('init', 'a2_j93ntx48v4gy');
@endauth
rdt('track', 'PageVisit');
// Livewire's wire:navigate swaps pages without a full load, which would
// otherwise leave those views invisible to the pixel. The URL check skips
// the livewire:navigated event fired on the initial page load.
if (!window.eregRedditNavigateTracking) {
    window.eregRedditNavigateTracking = true;
    (function () {
        var lastTracked = window.location.href;
        document.addEventListener('livewire:navigated', function () {
            if (window.location.href === lastTracked) return;
            lastTracked = window.location.href;
            rdt('track', 'PageVisit');
        });
    })();
}

oaiq("init", {
    pixelId: @js(config('services.openai_ads.pixel_id')),
    debug: {{ config('app.debug') ? 'true' : 'false' }},
@auth
    // OpenAI requires match keys pre-hashed (lowercase 64-char hex). Our
    // CAPI events hash the same way so the two sources reconcile.
    user: {
        email_sha256: @js(hash('sha256', mb_strtolower(trim(auth()->user()->email)))),
        external_id_sha256: @js(hash('sha256', (string) auth()->id())),
    },
@endauth
});

eregLoadScript('https://www.redditstatic.com/ads/pixel.js?pixel_id=a2_j93ntx48v4gy');
eregLoadScript('https://bzrcdn.openai.com/sdk/oaiq.min.js');
</script>
<!-- End Reddit Pixel and OpenAI Ads Pixel -->
@endif

<link rel="icon" type="image/x-icon" href="/img/favicon/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
<link rel="manifest" href="/img/favicon/site.webmanifest">

{{-- Clear any cached dark mode preference (one-time cleanup) --}}
<script>
    localStorage.removeItem('flux.appearance');document.documentElement.classList.remove('dark');
</script>

{{-- Inter from fonts.bunny.net. The font files come from the same host but
     are fetched in CORS mode, which uses its own (crossorigin) connection, so
     both preconnects are needed. 800 is the home page hero (font-extrabold).
     display=swap shows text in the fallback font while Inter downloads. --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

{{-- The marketing layouts pass $headScript = resources/js/marketing.js (Alpine
     only) unless the page renders Livewire. Every other layout gets app.js and
     loads Livewire and Flux itself through @fluxScripts. --}}
@vite(['resources/css/app.css', $headScript ?? 'resources/js/app.js'])
