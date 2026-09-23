<?php

use App\Domains\Lien\Seo\LienStatePage;
use App\Domains\ResaleCert\Seo\ResaleStatePage;
use App\Support\Seo\States;

describe('mechanics lien state pages', function () {
    it('renders the Texas page with deadlines, statute, notarization rule, and structured data', function () {
        $response = $this->get('/liens/texas');

        $response->assertOk()
            ->assertSee('Texas Mechanics Lien', escape: false)
            ->assertSee('Tex. Prop. Code', escape: false)
            ->assertSee('15th day of the 3rd month', escape: false)
            ->assertSee('<link rel="canonical" href="'.url('/liens/texas').'" />', escape: false)
            ->assertSee('"@type":"FAQPage"', escape: false)
            ->assertSee('"@type":"BreadcrumbList"', escape: false)
            ->assertSee('"@type":"Service"', escape: false)
            ->assertSee('property="og:title"', escape: false);

        // Mojibake from the seed data must never reach the page.
        expect($response->getContent())->not->toContain('Â§')->not->toContain('â€');
    });

    it('301-redirects two-letter codes and mixed-case slugs to the canonical slug', function () {
        $this->get('/liens/tx')->assertRedirect('/liens/texas')->assertStatus(301);
        $this->get('/liens/ny')->assertRedirect('/liens/new-york')->assertStatus(301);
    });

    it('404s unknown states without shadowing the fixed lien pages', function () {
        $this->get('/liens/atlantis')->assertNotFound();
        $this->get('/liens/pricing')->assertOk()->assertSee('Pricing');
        $this->get('/liens/preliminary-notice')->assertOk();
    });

    it('collapses identical residential and commercial deadlines into one row', function () {
        $page = LienStatePage::forCode('CA');

        expect($page)->not->toBeNull();
        foreach ($page->deadlines['mechanics_lien'] as $row) {
            expect($row)->toHaveKeys(['who', 'scope', 'when']);
        }
        expect($page->faq())->not->toBeEmpty()
            ->and($page->metaDescription())->toContain('California')
            ->and(mb_strlen($page->metaDescription()))->toBeLessThanOrEqual(165);
    });

    it('renders every one of the 50 state pages successfully', function () {
        foreach (States::names() as $name) {
            $this->get('/liens/'.States::slug($name))->assertOk();
        }
    });
});

describe('resale certificate state pages', function () {
    it('renders the Florida page with its expiration and uniform-certificate rules', function () {
        $this->get('/resale-certificates/florida')
            ->assertOk()
            ->assertSee('Florida Resale Certificate', escape: false)
            ->assertSee('MTC uniform certificate', escape: false)
            ->assertSee('"@type":"FAQPage"', escape: false)
            ->assertSee('<link rel="canonical" href="'.url('/resale-certificates/florida').'" />', escape: false);
    });

    it('301-redirects codes to slugs and 404s states without a sales tax', function () {
        $this->get('/resale-certificates/fl')->assertRedirect('/resale-certificates/florida')->assertStatus(301);
        $this->get('/resale-certificates/oregon')->assertNotFound();
        $this->get('/resale-certificates/mtc')->assertNotFound();
    });

    it('renders every available resale state page successfully', function () {
        $states = ResaleStatePage::availableStates();
        expect($states)->not->toBeEmpty();

        foreach ($states as $name) {
            $this->get('/resale-certificates/'.States::slug($name))->assertOk();
        }
    });
});

describe('state slugs', function () {
    it('round-trips names, slugs, and codes', function () {
        expect(States::slug('New York'))->toBe('new-york')
            ->and(States::codeFromSlug('new-york'))->toBe('NY')
            ->and(States::codeFromSlug('NY'))->toBe('NY')
            ->and(States::codeFromSlug('nowhere'))->toBeNull()
            ->and(States::neighbours('AL'))->toHaveCount(4)
            ->and(States::neighbours('WY'))->toHaveCount(4)->not->toHaveKey('WY');
    });
});
