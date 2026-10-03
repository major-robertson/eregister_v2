<?php

use App\Http\Controllers\SitemapController;
use App\Support\Seo\Urls;

/*
 * /about and /government/capabilities, and the owner's 2026-10-03 rule that
 * the street address is never published on the website.
 */

dataset('company pages', [
    'about' => ['/about', 'About eRegister | eRegister', 'About eRegister', 'About'],
    'capabilities' => ['/government/capabilities', 'Capabilities Statement for Government Agencies', 'Capabilities statement', 'Capabilities statement'],
]);

it('renders the page with its title, description, H1, breadcrumbs and canonical', function (string $path, string $title, string $h1, string $crumb) {
    $html = $this->get($path)->assertOk()->getContent();

    preg_match('/<meta name="description"\s+content="([^"]*)"/', $html, $description);
    $description = html_entity_decode($description[1] ?? '', ENT_QUOTES);

    expect($html)->toContain('<title>'.e($title).'</title>')
        ->toContain('<link rel="canonical" href="'.Urls::absolute($path).'" />')
        ->toContain('<nav aria-label="Breadcrumb"')
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('<span aria-current="page" class="font-medium">'.$crumb.'</span>')
        ->and(preg_match_all('/<h1[\s>]/', $html))->toBe(1)
        ->and(preg_match('/<h1[^>]*>\s*'.preg_quote($h1, '/').'\s*<\/h1>/', $html))->toBe(1)
        ->and(mb_strlen($description))->toBeGreaterThanOrEqual(70)->toBeLessThanOrEqual(165);
})->with('company pages');

it('lists both pages in the sitemap', function () {
    $entries = collect(SitemapController::urls())->keyBy('loc');

    expect($entries)->toHaveKey(url('/about'))
        ->toHaveKey(url('/government/capabilities'))
        ->and($entries[url('/about')]['priority'])->toBe('0.5');
});

it('publishes no address, identifiers or email address on either page', function (string $path) {
    $html = $this->get($path)->assertOk()->getContent();
    $text = strip_tags(preg_replace('/<(script|style)\b.*?<\/\1>/si', ' ', $html));

    expect($html)->not->toContain('Brownsboro')
        ->not->toContain('40207')
        ->not->toContain('mailto:')
        ->and(preg_match('/\b(UEI|CAGE|NAICS|DUNS)\b/', $html))->toBe(0)
        ->and(preg_match('/[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}/', $html))->toBe(0)
        ->and($text)->not->toContain('Louisville')
        ->not->toContain('Kentucky');
})->with(['/about', '/government/capabilities']);

it('backs the about page with the Google rating, the real reviews and the config facts', function () {
    $response = $this->get('/about')->assertOk();

    $response->assertSee('on Google')
        ->assertSee('In business since '.config('company.in_business_since'))
        ->assertSee(number_format(config('company.businesses_helped')).'+')
        ->assertSee('As of '.\Illuminate\Support\Carbon::parse(config('company.businesses_helped_as_of'))->format('F Y'))
        ->assertSee(route('contact'), false);

    foreach (config('company.google_reviews.featured') as $review) {
        $response->assertSee($review['name']);
    }
});

it('links the capabilities statement to every service page, the concept builds and the contact page', function () {
    $response = $this->get('/government/capabilities')->assertOk();

    foreach (['website-redesign', 'accessibility', 'cms', 'hosting', 'maintenance', 'portals', 'integrations', 'implementation'] as $service) {
        $response->assertSee('href="'.route('government.'.$service).'"', false);
    }

    $response->assertSee(route('clay-demo.home'), false)
        ->assertSee(route('mdcps-demo.home'), false)
        ->assertSee(route('government.florida-eog-demo-1'), false)
        ->assertSee('U.S.-based')
        ->assertSee('in business since '.config('company.in_business_since'))
        ->assertSee(route('contact'), false);

    expect(substr_count($response->getContent(), 'Concept build, not a client engagement'))->toBe(3);
});

it('links the new pages from the footers and the government home page', function () {
    $this->get('/')->assertOk()->assertSee('href="'.route('about').'"', false);
    $this->get('/government')->assertOk()->assertSee('href="'.route('government.capabilities').'"', false);
    $this->get('/government/cms')->assertOk()->assertSee('href="'.route('government.capabilities').'"', false);
});

it('never publishes the street address', function (string $path) {
    $html = $this->get($path)->assertOk()->getContent();

    expect($html)->not->toContain('Brownsboro')
        ->not->toContain('PostalAddress')
        ->and(preg_match('/\b40207\b/', $html))->toBe(0);
})->with([
    'home' => '/',
    'contact' => '/contact',
    'privacy policy' => '/privacy-policy',
    'terms of service' => '/terms-of-service',
    'refund policy' => '/refund-policy',
    'government' => '/government',
    'lien waiver ads landing' => '/lp/lien-waiver/tx',
    'sales tax ads landing' => '/lp/sales-tax',
]);

it('points the legal pages at the contact page instead of a mailing address', function (string $path) {
    $this->get($path)->assertOk()
        ->assertSee('through our <a href="'.route('contact').'">contact page</a>', false);
})->with(['/privacy-policy', '/terms-of-service', '/refund-policy']);
