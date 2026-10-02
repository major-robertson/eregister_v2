<?php

use App\Support\Analytics\Gtag;

/*
| Page speed (EREG-12, decision D4). Marketing pages run an Alpine-only
| bundle, not Flux and Livewire; the ad tags load after the page, and the
| Reddit and OpenAI pixels only where a paid visit or a conversion happens.
*/

beforeEach(function () {
    // Livewire remembers across requests in one process that it rendered a
    // component, and would then inject its scripts into every later page.
    app('livewire')->flushState();
});

function headOf(string $html): string
{
    return substr($html, 0, (int) strpos($html, '</head>'));
}

describe('marketing bundle', function () {
    it('loads marketing.js and neither Livewire nor Flux', function (string $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect($html)
            ->toContain('/build/assets/marketing-')
            ->not->toContain('livewire.js')
            ->not->toContain('livewire.min.js')
            ->not->toContain('/flux/flux')
            ->not->toContain('wire:navigate');
    })->with([
        'home' => '/',
        'lien state page' => '/liens/texas',
        'government' => '/government',
    ]);

    it('keeps Livewire and Flux on pages that render a Livewire component', function () {
        $html = $this->get('/contact')->assertOk()->getContent();

        expect($html)
            ->toMatch('/livewire(\.min)?\.js/')
            ->toContain('/flux/flux')
            ->not->toContain('/build/assets/marketing-');
    });

    it('renders the FAQ accordions with Alpine instead of Flux', function (string $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect($html)
            ->toContain('x-collapse')
            ->toContain('/build/assets/marketing-')
            ->not->toContain('<ui-disclosure');
    })->with([
        'resale certificates' => '/resale-certificates',
        'sales tax registration' => '/sales-tax-registration',
    ]);

    it('gives the logos their size so they do not shift the layout', function () {
        $html = $this->get('/')->assertOk()->getContent();

        expect(substr_count($html, 'width="1538" height="520"'))->toBe(2);
    });

    it('lets text show in a fallback font while Inter loads', function () {
        $head = headOf($this->get('/')->assertOk()->getContent());

        expect($head)
            ->toContain('fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap')
            ->toContain('<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>');
    });
});

describe('ad tags in production', function () {
    beforeEach(function () {
        app()->detectEnvironment(fn () => 'production');
    });

    it('queues calls in an inline stub and fetches gtag.js only after the page has loaded', function () {
        $head = headOf($this->get('/')->assertOk()->getContent());

        $stub = strpos($head, 'function gtag(){dataLayer.push(arguments);}');
        $firstExternalScript = strpos($head, '<script src=') ?: strpos($head, '<script type="module"');

        expect($stub)->not->toBeFalse()
            ->and($firstExternalScript)->not->toBeFalse()
            ->and($stub)->toBeLessThan($firstExternalScript)
            ->and($head)->toContain("gtag('config', 'G-MSVBK7VE6P')")
            ->and($head)->toContain("gtag('config', 'AW-984288380')")
            ->and($head)->not->toContain('<script async src="https://www.googletagmanager.com')
            ->and(substr_count($head, 'googletagmanager.com/gtag/js'))->toBe(1)
            ->and(strpos($head, "window.addEventListener('load'"))->toBeLessThan(strpos($head, 'googletagmanager.com/gtag/js'));
    });

    it('leaves the Reddit and OpenAI pixels off the home, product, state and government pages', function (string $path) {
        $this->get($path)
            ->assertOk()
            ->assertSee('googletagmanager.com/gtag/js', false)
            ->assertDontSee('redditstatic.com', false)
            ->assertDontSee('bzrcdn.openai.com', false)
            ->assertDontSee("rdt('init'", false);
    })->with([
        'home' => '/',
        'product' => '/liens/lien-waivers',
        'lien state page' => '/liens/texas',
        'government' => '/government',
    ]);

    it('loads the Reddit and OpenAI pixels on sign-up and campaign pages, deferred', function (string $path) {
        $head = headOf($this->get($path)->assertOk()->getContent());

        expect($head)
            ->toContain("rdt('init', 'a2_j93ntx48v4gy')")
            ->toContain("eregLoadScript('https://www.redditstatic.com/ads/pixel.js")
            ->toContain("eregLoadScript('https://bzrcdn.openai.com/sdk/oaiq.min.js')")
            ->not->toContain('<script async src=');
    })->with([
        'register' => '/register',
        'paid search landing page' => '/lp/lien-waiver/tx',
    ]);

    it('still prints an event queued on the previous request, after the stub', function () {
        Gtag::queue('waiver_starter_submit', ['state' => 'TX']);

        $head = headOf($this->get('/register')->assertOk()->getContent());

        expect($head)->toContain("gtag('event', 'waiver_starter_submit'")
            ->and(strpos($head, 'function gtag(){dataLayer.push(arguments);}'))
            ->toBeLessThan(strpos($head, "gtag('event', 'waiver_starter_submit'"));
    });
});
