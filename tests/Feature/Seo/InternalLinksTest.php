<?php

/*
 * Crawl flow on the money pages: every product and lien service page links
 * to other site pages from its body copy (not just the header and footer),
 * shows the breadcrumb trail it describes in JSON-LD, and the waiver state
 * pages link across to the lien state page and the bordering states.
 */

/**
 * Distinct internal hrefs inside <main> (so never the header or footer),
 * minus the page itself, Home, in-page anchors, and sign-up / log-in.
 *
 * @return array<int, string>
 */
function internalBodyLinks(string $html, string $path): array
{
    preg_match('/<main\b[^>]*>(.*)<\/main>/s', $html, $main);
    preg_match_all('/<a\b[^>]*\bhref="([^"]*)"/', $main[1] ?? '', $hrefs);

    $self = url($path);
    $base = rtrim(url('/'), '/');

    return collect($hrefs[1])
        ->map(fn (string $href) => strtok(html_entity_decode($href), '#'))
        ->filter(fn ($href) => is_string($href) && str_starts_with($href, $base))
        // The page itself, and Home (every breadcrumb trail starts there).
        ->reject(fn (string $href) => in_array(rtrim($href, '/'), [rtrim($self, '/'), $base], true))
        ->reject(fn (string $href) => preg_match('#/(register|login)(\?|$)#', $href))
        ->unique()
        ->values()
        ->all();
}

dataset('product pages', [
    '/llc', '/corporation', '/dba', '/nonprofit', '/sole-proprietorship',
    '/registered-agent', '/annual-reports', '/ein-tax-id', '/operating-agreement',
    '/sales-tax-registration',
]);

dataset('lien service pages', [
    '/liens/preliminary-notice', '/liens/notice-of-intent-to-lien', '/liens/lien-release',
    '/liens/payment-demand-letter', '/liens/pricing', '/liens/lien-waivers',
]);

it('links product pages to at least three other site pages from the body', function (string $path) {
    $html = $this->get($path)->assertOk()->getContent();

    expect(count(internalBodyLinks($html, $path)))->toBeGreaterThanOrEqual(3);
})->with('product pages');

it('links lien service pages to other lien pages and the state pages from the body', function (string $path) {
    $html = $this->get($path)->assertOk()->getContent();
    $links = internalBodyLinks($html, $path);

    expect(count($links))->toBeGreaterThanOrEqual(3)
        ->and($links)->toContain(route('liens'))
        ->and($links)->toContain(route('liens.state', ['state' => 'texas']))
        ->and($links)->toContain(route('liens.state', ['state' => 'wyoming']));
})->with('lien service pages');

it('shows a visible breadcrumb trail on every product page', function (string $path) {
    $this->get($path)
        ->assertOk()
        ->assertSee('<nav aria-label="Breadcrumb"', escape: false)
        ->assertSee('"@type":"BreadcrumbList"', escape: false);
})->with('product pages');

it('links the Texas waiver page to the Texas lien page and its bordering states', function () {
    $html = $this->get('/liens/lien-waivers/tx')->assertOk()->getContent();
    $links = internalBodyLinks($html, '/liens/lien-waivers/tx');

    expect($links)->toContain(route('liens.state', ['state' => 'texas']))
        ->toContain(route('liens.lien-waivers'));

    foreach (['ar', 'la', 'nm', 'ok'] as $border) {
        expect($links)->toContain(route('liens.lien-waivers.state', ['state' => $border]));
    }

    expect($html)->toContain('Oklahoma lien waiver forms')
        ->toContain('Texas mechanics lien deadlines')
        ->toContain('<nav aria-label="Breadcrumb"');
});

it('names the state links on the hubs after what they lead to', function () {
    $this->get('/liens')->assertOk()->assertSee('Texas mechanics lien');
    $this->get('/liens/lien-waivers')->assertOk()->assertSee('Texas lien waiver forms');
});
