<?php

it('emits canonical, description, open graph, and organization schema on marketing pages', function (string $uri) {
    $response = $this->get($uri)->assertOk();
    $html = $response->getContent();

    $response
        ->assertSee('<link rel="canonical" href="'.url($uri === '/' ? '' : $uri).'" />', escape: false)
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
