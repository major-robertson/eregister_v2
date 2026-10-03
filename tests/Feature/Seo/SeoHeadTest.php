<?php

use App\Support\Seo\Urls;

it('emits canonical, description, open graph, and organization schema on marketing pages', function (string $uri) {
    $response = $this->get($uri)->assertOk();
    $html = $response->getContent();

    $response
        ->assertSee('<link rel="canonical" href="'.Urls::absolute($uri).'" />', escape: false)
        ->assertSee('<meta name="description" content="', escape: false)
        ->assertSee('property="og:title"', escape: false)
        ->assertSee('property="og:image" content="'.asset('img/og/default.png').'"', escape: false)
        ->assertSee('name="twitter:card" content="summary_large_image"', escape: false)
        ->assertSee('"@type":"Organization"', escape: false)
        ->assertDontSee('noindex', escape: false);

    // Exactly one description tag, the charset in the first 1024 bytes, and the
    // title ahead of every script: the head partial puts the document metadata
    // before the analytics and ad tags.
    expect(substr_count($html, '<meta name="description"'))->toBe(1)
        ->and(strpos($html, '<meta charset="utf-8" />'))->toBeLessThan(1024)
        ->and(strpos($html, '<title>'))->toBeLessThan(strpos($html, '<script'));
})->with([
    // The pages this branch moves onto the shared SEO head. The other
    // marketing pages keep their inline description tags until their own
    // SEO pass lands, so they are not in this dataset yet.
    'liens' => '/liens',
    'lien-waivers' => '/liens/lien-waivers',
    'resale-certificates' => '/resale-certificates',
    'resale-state' => '/resale-certificates/florida',
    'lien-state' => '/liens/texas',
]);

it('drops the query string from the canonical url', function () {
    $this->get('/llc?utm_source=reddit&utm_campaign=x')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.url('/llc').'" />', escape: false)
        ->assertSee('property="og:url" content="'.url('/llc').'"', escape: false);
});

it('emits only well-formed JSON-LD blocks', function (string $uri) {
    $html = $this->get($uri)->assertOk()->getContent();

    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);
    expect($blocks[1])->not->toBeEmpty();

    foreach ($blocks[1] as $json) {
        $data = json_decode($json, true);
        expect($data)->toBeArray()->toHaveKey('@type');
        expect(json_last_error())->toBe(JSON_ERROR_NONE);
    }
})->with([
    'home' => '/',
    'dba' => '/dba',
    'liens' => '/liens',
    'lien-waivers-pricing' => '/liens/lien-waivers/pricing',
    'lien-state' => '/liens/texas',
    'resale-state' => '/resale-certificates/florida',
    'government-cms' => '/government/cms',
]);

it('gives the home page a canonical with a trailing slash, matching the sitemap root', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/" />', escape: false);

    expect(array_column(App\Http\Controllers\SitemapController::urls(), 'loc'))->toContain('http://localhost/');
});

it('canonicalises a copy served through /index.php to the real url', function () {
    // What nginx hands PHP for "/index.php/llc": the script name is the
    // front controller and the route sees "/llc".
    $this->withServerVariables([
        'SCRIPT_NAME' => '/index.php',
        'SCRIPT_FILENAME' => public_path('index.php'),
    ])->get('/index.php/llc')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/llc" />', escape: false)
        ->assertSee('property="og:url" content="http://localhost/llc"', escape: false)
        ->assertSee('"@id":"http://localhost/#organization"', escape: false);
    // Ordinary links on that copy still carry /index.php; the nginx redirect
    // (a Forge change) is what removes the copy itself.
});

it('builds the canonical and organization ids from the configured app url', function () {
    config(['app.url' => 'https://example.test']);

    $this->get('/llc')
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.Urls::absolute('/llc').'" />', escape: false)
        ->assertSee('<link rel="canonical" href="https://example.test/llc" />', escape: false)
        ->assertSee('"@id":"https://example.test/#organization"', escape: false);
});

it('describes the organization with its founding year and service area, and no address, email or phone', function () {
    $html = $this->get('/llc')->assertOk()->getContent();

    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);
    $organization = collect($blocks[1])
        ->map(fn ($json) => json_decode($json, true))
        ->firstWhere('@type', 'Organization');

    expect($organization)->not->toBeNull()
        ->and($organization['foundingDate'])->toBe('2013')
        ->and($organization['areaServed'])->toBe('US')
        ->and($organization['contactPoint']['url'])->toBe('http://localhost/contact')
        ->and($organization['description'])->toBeString()
        ->and($organization)->toHaveKeys(['name', 'url', 'logo', 'description', 'foundingDate', 'contactPoint'])
        ->not->toHaveKey('address')
        ->not->toHaveKey('email')
        ->not->toHaveKey('telephone')
        ->not->toHaveKey('sameAs')
        ->not->toHaveKey('legalName');

    expect($html)->not->toContain('PostalAddress')
        ->not->toContain('Brownsboro')
        ->not->toContain('40207');
});
