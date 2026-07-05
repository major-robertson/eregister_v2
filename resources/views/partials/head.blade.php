{{-- No ad tags on admin pages or the third-party sales demos (simulated
     MDCPS / Florida EOG sites shown to procurement evaluators). --}}
@unless(request()->routeIs('admin.*', 'mdcps-demo.*', 'government.florida-eog-demo-*'))
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-MSVBK7VE6P"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-MSVBK7VE6P');
  gtag('config', 'AW-984288380');
</script>

<!-- Reddit Pixel -->
<script>
!function(w,d){if(!w.rdt){var p=w.rdt=function(){p.sendEvent?p.sendEvent.apply(p,arguments):p.callQueue.push(arguments)};p.callQueue=[];var t=d.createElement("script");t.src="https://www.redditstatic.com/ads/pixel.js?pixel_id=a2_j93ntx48v4gy",t.async=!0;var s=d.getElementsByTagName("script")[0];s.parentNode.insertBefore(t,s)}}(window,document);
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
(function () {
    var lastTracked = window.location.href;
    document.addEventListener('livewire:navigated', function () {
        if (window.location.href === lastTracked) return;
        lastTracked = window.location.href;
        rdt('track', 'PageVisit');
    });
})();
</script>
<!-- DO NOT MODIFY UNLESS TO REPLACE A USER IDENTIFIER -->
<!-- End Reddit Pixel -->

<!-- OpenAI (ChatGPT) Ads Pixel -->
<script>
!function(w,d,s,u){if(w.oaiq)return;var q=function(){q.q.push(arguments)};q.q=[];w.oaiq=q;var j=d.createElement(s);j.async=1;j.src=u;var f=d.getElementsByTagName(s)[0];f.parentNode.insertBefore(j,f)}(window,document,"script","https://bzrcdn.openai.com/sdk/oaiq.min.js");
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
</script>
<!-- End OpenAI Ads Pixel -->
@endunless

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" type="image/x-icon" href="/img/favicon/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png">
<link rel="manifest" href="/img/favicon/site.webmanifest">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

{{-- Clear any cached dark mode preference (one-time cleanup) --}}
<script>
    localStorage.removeItem('flux.appearance');document.documentElement.classList.remove('dark');
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])