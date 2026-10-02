<?php

use App\Domains\ResaleCert\Seo\ResaleStateContent;
use App\Domains\ResaleCert\Seo\ResaleStatePage;

dataset('no sales tax states', [
    'Oregon' => ['OR', 'oregon', 'Oregon Department of Revenue', 'Form 150-800-002'],
    'Montana' => ['MT', 'montana', 'Montana Department of Revenue', 'Montana Business Registry Resale Certificate'],
    'New Hampshire' => ['NH', 'new-hampshire', 'New Hampshire Department of Revenue Administration', 'MTC uniform certificate'],
    'Delaware' => ['DE', 'delaware', 'Delaware Division of Revenue', 'MTC uniform certificate'],
]);

if (! function_exists('noSalesTaxJsonLd')) {
    /** @return array<int, array<string, mixed>> every JSON-LD block on the page, decoded */
    function noSalesTaxJsonLd(string $html): array
    {
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);

        return array_map(fn (string $json) => json_decode($json, true), $m[1]);
    }
}

describe('no-sales-tax resale pages', function () {
    it('renders with the agency, the no-tax wording, what to give suppliers and local taxes', function (string $code, string $slug, string $agency, string $supplierMention) {
        $page = ResaleStatePage::forCode($code);

        $response = $this->get("/resale-certificates/{$slug}")->assertOk()
            ->assertSee($agency, escape: false)
            ->assertSee("{$page->name} has no statewide sales tax", escape: false)
            ->assertSee('No Sales Tax: What Suppliers Need', escape: false)
            ->assertSee('What to give suppliers in other states', escape: false)
            ->assertSee('Local taxes to know about', escape: false)
            ->assertSee("{$page->name} rules in plain English", escape: false)
            ->assertSee($supplierMention, escape: false)
            ->assertSee('<link rel="canonical" href="'.url("/resale-certificates/{$slug}").'" />', escape: false)
            ->assertSee(route('sales-tax-registration'), escape: false)
            ->assertDontSee('Start generating certificates')
            ->assertDontSee('Generate signed certificates');

        $html = $response->getContent();
        $blocks = noSalesTaxJsonLd($html);

        // No Service or Offer: we sell nothing for a state with no certificate.
        expect($html)->not->toContain('"@type":"Offer"')
            ->not->toContain('"@type":"Service"');

        $faq = collect($blocks)->firstWhere('@type', 'FAQPage');
        expect($faq)->not->toBeNull()
            ->and($faq['mainEntity'])->toHaveCount(4)
            ->and(collect($faq['mainEntity'])->pluck('name')->all())->toBe([
                "Do I need a resale certificate in {$page->name}?",
                "What do I give an out-of-state supplier as {$page->article} {$page->name} business?",
                "Can {$page->article} {$page->name} business use the MTC uniform certificate?",
                "Do I charge sales tax to my customers in {$page->name}?",
            ]);

        $breadcrumbs = collect($blocks)->firstWhere('@type', 'BreadcrumbList');
        expect($breadcrumbs)->not->toBeNull()
            ->and(collect($breadcrumbs['itemListElement'])->pluck('name')->all())->toBe(['Home', 'Resale Certificates', $page->name]);

        foreach ($page->localTaxes() as $tax) {
            $response->assertSee($tax['name'], escape: false);
        }
    })->with('no sales tax states');

    it('301-redirects the two-letter code to the slug', function (string $code, string $slug) {
        $this->get('/resale-certificates/'.strtolower($code))
            ->assertStatus(301)
            ->assertRedirect("/resale-certificates/{$slug}");
    })->with('no sales tax states');

    it('keeps titles and descriptions within budget', function (string $code) {
        $page = ResaleStatePage::forCode($code);

        expect($page->noSalesTax())->toBeTrue()
            ->and(mb_strlen($page->title()))->toBeLessThanOrEqual(60, $page->title())
            ->and($page->title())->toContain("{$page->name} Resale Certificate")->toContain('No Sales Tax')
            ->and(mb_strlen($page->metaDescription()))->toBeGreaterThanOrEqual(70, $page->metaDescription())
            ->toBeLessThanOrEqual(165, $page->metaDescription())
            ->and($page->metaDescription())->toContain("{$page->name} has no sales tax");
    })->with('no sales tax states');

    it('names Oregon\'s own form in the description and links its PDF', function () {
        expect(ResaleStatePage::forCode('OR')->metaDescription())->toContain('Form 150-800-002');

        $this->get('/resale-certificates/oregon')
            ->assertSee('href="https://www.oregon.gov/dor/forms/FormsPubs/or-business-registry-resale-cert_800-002.pdf"', escape: false)
            ->assertSee('Oregon Business Registry number', escape: false);
    });

    it('has a no-tax content file with the four FAQ answers for each state', function (string $code) {
        $data = ResaleStateContent::for($code);

        expect($data['no_sales_tax'])->toBeTrue()
            ->and($data['no_sales_tax_note'])->toBeString()->not->toBeEmpty()
            ->and($data['suppliers'])->not->toBeEmpty()
            ->and($data['local_taxes'])->not->toBeEmpty()
            ->and(array_keys($data['faq']))->toBe(['need_certificate', 'what_to_give', 'mtc', 'charge_customers']);

        foreach ($data['local_taxes'] as $tax) {
            expect($tax['source_url'])->toStartWith('https://');
        }
    })->with('no sales tax states');

    it('survives a cache round trip under a versioned key', function () {
        $page = ResaleStatePage::forCode('MT');
        $copy = unserialize(serialize($page));

        expect(ResaleStatePage::cacheKey('mt'))->toBe('seo.resale-state.v'.ResaleStatePage::CACHE_VERSION.'.MT')
            ->and($copy->noSalesTax())->toBeTrue()
            ->and($copy->title())->toBe($page->title())
            ->and($copy->faq())->toBe($page->faq());
    });
});

describe('no-sales-tax states in the hub and sitemap', function () {
    it('lists them in availableStates, the hub grid and the sitemap', function () {
        $states = ResaleStatePage::availableStates();

        expect($states)->toHaveKeys(['OR', 'MT', 'NH', 'DE'])
            ->not->toHaveKey('MTC')
            ->and(array_values($states))->toBe(collect($states)->sort()->values()->all());

        $hub = $this->get('/resale-certificates')->assertOk();
        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();

        foreach (['oregon', 'montana', 'new-hampshire', 'delaware'] as $slug) {
            $hub->assertSee(route('resale-certificates.state', ['state' => $slug]), escape: false);
            expect($sitemap)->toContain('<loc>'.url("/resale-certificates/{$slug}").'</loc>');
        }

        $hub->assertSee('No sales tax', escape: false);
    });

    it('still 404s a state with no content file and no rule row', function () {
        expect(ResaleStatePage::forCode('ZZ'))->toBeNull();
        $this->get('/resale-certificates/atlantis')->assertNotFound();
    });
});
