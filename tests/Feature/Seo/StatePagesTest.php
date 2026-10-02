<?php

use App\Domains\Lien\Seo\LienStatePage;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
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
            ->assertSee('Form DR-13', escape: false)
            ->assertSee('Florida Department of Revenue', escape: false)
            ->assertSee('Florida rules in plain English', escape: false)
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

describe('cached state pages', function () {
    // Tests run on the array cache store, which never serializes; prod uses
    // the database store, so the cached object must survive a round trip.
    it('survive a serialize round trip', function () {
        foreach ([LienStatePage::forCode('TX'), ResaleStatePage::forCode('FL')] as $page) {
            $copy = unserialize(serialize($page));

            expect($copy->title())->toBe($page->title())
                ->and($copy->metaDescription())->toBe($page->metaDescription())
                ->and($copy->keyFacts())->toBe($page->keyFacts());
        }
    });

    it('caches under versioned keys', function () {
        expect(LienStatePage::cacheKey('tx'))->toBe('seo.lien-state.v3.TX')
            ->and(ResaleStatePage::cacheKey('fl'))->toBe('seo.resale-state.v3.FL')
            ->and(ResaleStatePage::statesCacheKey())->toBe('seo.resale-states.v3');
    });
});

describe('state page titles and descriptions', function () {
    it('composes every resale description within budget without truncating', function () {
        foreach (array_keys(ResaleStatePage::availableStates()) as $code) {
            $page = ResaleStatePage::forCode($code);
            $description = $page->metaDescription();

            expect(mb_strlen($description))->toBeGreaterThanOrEqual(70, "{$code}: {$description}")
                ->toBeLessThanOrEqual(165, "{$code}: {$description}")
                ->and($description)->not->toEndWith('...')
                ->toContain($page->name)
                ->toContain('Generate signed certificates')
                ->and(mb_strlen($page->title()))->toBeLessThanOrEqual(70, $page->title());
        }
    });

    it('keeps every waiver page title and description within budget', function () {
        foreach (array_keys(WaiverStateRegistry::STATE_NAMES) as $code) {
            $html = $this->get('/liens/lien-waivers/'.strtolower($code))->assertOk()->getContent();

            preg_match('/<title>(.*?)<\/title>/s', $html, $title);
            preg_match('/<meta name="description" content="([^"]*)"/', $html, $description);
            $title = html_entity_decode($title[1] ?? '', ENT_QUOTES);
            $description = html_entity_decode($description[1] ?? '', ENT_QUOTES);

            expect(mb_strlen($title))->toBeLessThanOrEqual(60, "{$code}: {$title}")
                ->and(mb_strlen($description))->toBeGreaterThanOrEqual(70, "{$code}: {$description}")
                ->toBeLessThanOrEqual(160, "{$code}: {$description}")
                ->and($description)->not->toEndWith('...');
        }
    });
});

describe('indefinite articles', function () {
    it('never says "a Alabama" on any state page set', function () {
        $paths = [];
        foreach (['AL', 'OH', 'ID', 'OR'] as $code) {
            $slug = States::slug(States::name($code));
            $paths[] = "/liens/{$slug}";
            $paths[] = '/liens/lien-waivers/'.strtolower($code);
            // Oregon has no sales tax, so no resale page.
            if (isset(ResaleStatePage::availableStates()[$code])) {
                $paths[] = "/resale-certificates/{$slug}";
            }
        }

        foreach ($paths as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $text = html_entity_decode(strip_tags($html), ENT_QUOTES);

            expect(preg_match('/\ba (?:Alabama|Ohio|Idaho|Oregon)\b/', $text, $match))
                ->toBe(0, "{$path}: \"".($match[0] ?? '').'"');
        }
    });

    it('uses "an" for Alabama on the resale page copy', function () {
        $this->get('/resale-certificates/alabama')
            ->assertOk()
            ->assertSee('an Alabama resale certificate', escape: false);
    });
});

describe('lien page details', function () {
    it('sends attorney-referral states to the attorney CTA with no price or Offer', function () {
        $html = $this->get('/liens/delaware')->assertOk()->getContent();

        expect($html)->not->toContain('"@type":"Offer"')
            ->not->toContain('$99')
            ->toContain('Get matched with a Delaware lien attorney')
            ->toContain('Request a Delaware lien attorney');
    });

    it('keeps the Texas monthly deadline wording and the counsel disclaimer', function () {
        $this->get('/liens/texas')
            ->assertOk()
            ->assertSee('15th day of the 3rd month', escape: false)
            ->assertSee('Confirm with counsel', escape: false);
    });

    it('states the researched Texas office, enforcement deadline, and fraudulent-lien statute', function () {
        // Tex. Prop. Code §§ 53.052, 53.158 (HB 2237); Tex. Civ. Prac. & Rem. Code § 12.002.
        $this->get('/liens/texas')
            ->assertOk()
            ->assertSee('county clerk', escape: false)
            ->assertSee('within 1 year after the last day the lien could have been filed', escape: false)
            ->assertSee('12.002', escape: false)
            ->assertDontSee('within 1 year after the lien is recorded', escape: false);
    });

    it('states the South Dakota enforcement period from the last item furnished, as SDCL 44-9-24 reads', function () {
        $this->get('/liens/south-dakota')
            ->assertOk()
            ->assertSee('within 6 years after last furnishing labor or materials', escape: false)
            ->assertDontSee('within 6 years after the lien is recorded', escape: false);
    });

    it('omits the enforcement fact where it cannot be stated as a date', function () {
        // Delaware: the statement of claim is itself the suit. Hawaii, Maryland and
        // Alabama run from a court order, a petition, or the debt's maturity.
        foreach (['DE', 'HI', 'MD', 'AL'] as $code) {
            $page = LienStatePage::forCode($code);

            expect($page->enforcementSentence())->toBeNull($code)
                ->and(array_column($page->keyFacts(), 'label'))->not->toContain('Enforcement deadline')
                ->and(array_column($page->faq(), 'q'))->each->not->toContain('valid?');
        }
    });

    it('renders Delaware, New Hampshire and Rhode Island without a "See statute" fallback', function () {
        foreach (['delaware', 'new-hampshire', 'rhode-island'] as $slug) {
            $this->get("/liens/{$slug}")
                ->assertOk()
                ->assertDontSee('See statute', escape: false)
                ->assertSee('Practitioner notes for', escape: false);
        }
    });

    it('keeps proper-noun filing offices capitalized mid-sentence', function () {
        // Iowa's office is held back from the seed data (it feeds the generated documents); set it here.
        LienStatePage::forCode('IA')->rule->update(['filing_location' => "Iowa Secretary of State's Mechanics' Notice and Lien Registry (MNLR), online"]);

        expect(LienStatePage::forCode('IA')->filingLocationPhrase())->toStartWith('Iowa Secretary of State')
            ->and(LienStatePage::forCode('AL')->filingLocationPhrase())->toStartWith('office of the judge of probate')
            ->and(LienStatePage::forCode('TX')->filingLocationPhrase())->toBe('county clerk where the property sits');
    });
});
