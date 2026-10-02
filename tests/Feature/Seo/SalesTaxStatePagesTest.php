<?php

use App\Domains\SalesTax\Seo\SalesTaxStateContent;
use App\Domains\SalesTax\Seo\SalesTaxStatePage;
use App\Http\Controllers\SitemapController;
use App\Models\Price;
use App\Support\Seo\States;

if (! function_exists('salesTaxPageBodyText')) {
    /** The visible text of a page's <main> element: scripts, styles and SVGs dropped, tags stripped. */
    function salesTaxPageBodyText(string $html): string
    {
        $main = preg_match('/<main\b[^>]*>(.*)<\/main>/s', $html, $m) ? $m[1] : $html;
        $main = preg_replace('/<(script|style|svg)\b[^>]*>.*?<\/\1>/s', ' ', $main);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($main), ENT_QUOTES | ENT_HTML5)));
    }
}

if (! function_exists('salesTaxPageShingles')) {
    /** @return array<string, true> 6-word shingles of the text, as set keys. */
    function salesTaxPageShingles(string $text): array
    {
        $words = preg_split('/\s+/u', mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text)), -1, PREG_SPLIT_NO_EMPTY);
        $set = [];
        for ($i = 0, $n = count($words) - 5; $i < $n; $i++) {
            $set[implode(' ', array_slice($words, $i, 6))] = true;
        }

        return $set;
    }
}

if (! function_exists('salesTaxPageRenderAll')) {
    /** @return array<string, string> code => rendered HTML for every sales tax state page */
    function salesTaxPageRenderAll($test): array
    {
        $pages = [];
        foreach (SalesTaxStatePage::availableStates() as $code => $name) {
            $pages[$code] = $test->get('/sales-tax-registration/'.States::slug($name))->assertOk()->getContent();
        }

        return $pages;
    }
}

/** The 18 states with a state definition file in the registration flow. */
const SALES_TAX_DEFINITION_STATES = ['CA', 'CT', 'FL', 'GA', 'IL', 'MD', 'MI', 'MO', 'NC', 'NJ', 'NY', 'OH', 'OK', 'PA', 'TN', 'TX', 'WA', 'WI'];

beforeEach(function () {
    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'per_state', 'billing_type' => 'one_time'],
        ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
    );
});

describe('sales tax state content files', function () {
    it('has a file for the 45 sales tax states and DC, and none for the five without a sales tax', function () {
        $codes = array_keys(SalesTaxStatePage::availableStates());

        expect($codes)->toHaveCount(46)
            ->toContain('TX', 'DC', 'CA', 'WY')
            ->not->toContain('AK', 'DE', 'MT', 'NH', 'OR');

        expect(SalesTaxStateContent::for('ZZ'))->toBeNull()
            ->and(SalesTaxStateContent::for('../x'))->toBeNull();
    });

    it('gives every file an agency, a term, a threshold, plain-English notes and sourced facts', function () {
        $files = glob(SalesTaxStateContent::directory().'/*.php');
        expect($files)->toHaveCount(46);

        foreach ($files as $file) {
            $code = strtoupper(basename($file, '.php'));
            $data = require $file;

            expect($data['state'])->toBe($code)
                ->and($data['name'] ?? null)->toBeString()->not->toBeEmpty()
                ->and($data['agency']['name'] ?? null)->toBeString()->not->toBeEmpty()
                ->and($data['agency']['url'] ?? '')->toStartWith('https://')
                ->and($data['registration']['term'] ?? null)->toBeString()->not->toBeEmpty()
                ->and($data['nexus']['economic']['revenue_usd'] ?? null)->toBeInt()->toBeGreaterThan(0)
                ->and($data)->not->toHaveKey('existing_ads_facts_check')
                ->and($data)->not->toHaveKey('unconfirmed');

            $words = str_word_count($data['state_notes'] ?? '');
            expect($words)->toBeGreaterThanOrEqual(120, "{$code}: state_notes has {$words} words")
                ->toBeLessThanOrEqual(300, "{$code}: state_notes has {$words} words");

            expect(count($data['facts'] ?? []))->toBeGreaterThanOrEqual(3, "{$code}: fewer than 3 facts");
            foreach ($data['facts'] as $fact) {
                expect($fact['text'])->not->toBeEmpty()
                    ->and($fact['source_url'])->toStartWith('https://');
            }
        }
    });
});

describe('sales tax state pages', function () {
    it('renders the Texas page with the permit, agency, fee, threshold, form and structured data', function () {
        $html = $this->get('/sales-tax-registration/texas')
            ->assertOk()
            ->assertSee('<h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">Texas Sales Tax Registration</h1>', escape: false)
            ->assertSee('Sales and Use Tax Permit', escape: false)
            ->assertSee('Texas Comptroller of Public Accounts', escape: false)
            ->assertSee('No state fee', escape: false)
            ->assertSee('$500,000 in sales', escape: false)
            ->assertSee('Form AP-201', escape: false)
            ->assertSee('How to register in Texas', escape: false)
            ->assertSee('After you register', escape: false)
            ->assertSee('Texas rules in plain English', escape: false)
            ->assertSee('More Texas facts', escape: false)
            ->assertSee('href="https://comptroller.texas.gov/forms/ap-201.pdf" rel="noopener" target="_blank"', escape: false)
            ->assertSee('"@type":"FAQPage"', escape: false)
            ->assertSee('"@type":"BreadcrumbList"', escape: false)
            ->assertSee('"@type":"Service"', escape: false)
            ->assertSee('<link rel="canonical" href="'.url('/sales-tax-registration/texas').'" />', escape: false)
            ->assertSee('State rules last checked October 2, 2026.', escape: false)
            ->getContent();

        expect($html)->not->toContain('nofollow')
            ->and(SalesTaxStatePage::forCode('TX')->title())->toBe('Texas Sales Tax Registration | Sales and Use Tax Permit');
    });

    it('301-redirects codes and wrong-case slugs, 404s states without a page, and keeps the hub', function () {
        $this->get('/sales-tax-registration/tx')->assertRedirect('/sales-tax-registration/texas')->assertStatus(301);
        $this->get('/sales-tax-registration/TX')->assertRedirect('/sales-tax-registration/texas')->assertStatus(301);
        $this->get('/sales-tax-registration/New-York')->assertRedirect('/sales-tax-registration/new-york')->assertStatus(301);
        $this->get('/sales-tax-registration/dc')->assertRedirect('/sales-tax-registration/district-of-columbia')->assertStatus(301);
        $this->get('/sales-tax-registration/oregon')->assertNotFound();
        $this->get('/sales-tax-registration/alaska')->assertNotFound();
        $this->get('/sales-tax-registration/nowhere')->assertNotFound();
        $this->get('/sales-tax-registration')->assertOk();
    });

    it('renders every available state page', function () {
        expect(salesTaxPageRenderAll($this))->toHaveCount(46);
    });

    it('names the agency and form on every page and keeps research notes off it', function () {
        foreach (salesTaxPageRenderAll($this) as $code => $html) {
            $content = SalesTaxStateContent::for($code);
            $text = salesTaxPageBodyText($html);

            expect(str_contains($text, $content['agency']['name']))->toBeTrue("{$code}: agency name missing");

            if ($number = $content['registration']['form']['number'] ?? null) {
                expect(str_contains($text, $number))->toBeTrue("{$code}: form number missing");
            }

            expect(preg_match('/not confirmed|secondary (?:sources|sites)|pages reviewed|read today|resale (?:certificate )?research|was found|automated reads/i', $text, $match))
                ->toBe(0, "{$code}: research note \"".($match[0] ?? '').'"');
        }
    });

    it('reads differently from every other state page', function () {
        $sets = [];
        foreach (salesTaxPageRenderAll($this) as $code => $html) {
            $sets[$code] = salesTaxPageShingles(salesTaxPageBodyText($html));
        }

        $codes = array_keys($sets);
        foreach ($codes as $i => $a) {
            foreach (array_slice($codes, $i + 1) as $b) {
                $shared = count(array_intersect_key($sets[$a], $sets[$b]));
                $jaccard = $shared / max(1, count($sets[$a]) + count($sets[$b]) - $shared);

                expect($jaccard)->toBeLessThanOrEqual(0.8, sprintf('%s and %s are %.0f%% identical', $a, $b, $jaccard * 100));
            }
        }
    });

    it('offers our registration with the catalog price where the flow takes the state, and a guide elsewhere', function () {
        foreach (salesTaxPageRenderAll($this) as $code => $html) {
            $page = SalesTaxStatePage::forCode($code);

            if ($code === 'DC') {
                expect($page->filesWithUs())->toBeFalse()
                    ->and($html)->toContain('Register for District of Columbia sales tax yourself with this guide, or ask us')
                    ->toContain('does not cover the District of Columbia yet')
                    ->not->toContain('We register you in')
                    ->not->toContain('$199 per state')
                    ->not->toContain('"@type":"Offer"');

                continue;
            }

            expect($page->filesWithUs())->toBeTrue("{$code}: not offered by the registration flow")
                ->and($html)->toContain('We register you in '.$page->inName)
                ->toContain('$199 per state')
                ->toContain('"@type":"Offer"')
                ->toContain('/register?product=sales-tax&amp;state='.$code)
                ->not->toContain('yourself with this guide');
        }

        // The 18 states with their own definition file are all among them.
        foreach (SALES_TAX_DEFINITION_STATES as $code) {
            expect(is_file(app_path("Domains/Forms/Definitions/SalesTaxPermit/{$code}.php")))->toBeTrue($code)
                ->and(SalesTaxStatePage::forCode($code)->filesWithUs())->toBeTrue($code);
        }
    });

    it('drops our price and the Offer when the catalog has no price', function () {
        Price::query()->where('product_key', 'sales_tax_permit')->where('variant_key', 'per_state')->delete();

        $this->get('/sales-tax-registration/texas')
            ->assertOk()
            ->assertSee('We register you in Texas', escape: false)
            ->assertDontSee('per state. Any state fee is separate.', escape: false)
            ->assertDontSee('"@type":"Offer"', escape: false);
    });

    it('composes every title and description within budget without truncating', function () {
        foreach (array_keys(SalesTaxStatePage::availableStates()) as $code) {
            $page = SalesTaxStatePage::forCode($code);
            $description = $page->metaDescription();

            expect(mb_strlen($page->title()))->toBeLessThanOrEqual(60, $page->title())
                ->and($page->title())->toStartWith($page->name.' Sales Tax')
                ->and(mb_strlen($description))->toBeGreaterThanOrEqual(70, "{$code}: {$description}")
                ->toBeLessThanOrEqual(165, "{$code}: {$description}")
                ->and($description)->not->toEndWith('...')
                ->toContain($page->name)
                ->toContain('remote sellers register after $');
        }
    });

    it('says "the District of Columbia" after "in"', function () {
        $text = salesTaxPageBodyText($this->get('/sales-tax-registration/district-of-columbia')->assertOk()->getContent());

        expect($text)->toContain('How to register in the District of Columbia')
            ->not->toMatch('/\bin District of Columbia\b/');
    });

    it('caches under versioned keys and survives a serialize round trip', function () {
        $page = SalesTaxStatePage::forCode('TX');
        $copy = unserialize(serialize($page));

        expect(SalesTaxStatePage::cacheKey('tx'))->toBe('seo.sales-tax-state.v1.TX')
            ->and(SalesTaxStatePage::statesCacheKey())->toBe('seo.sales-tax-states.v1')
            ->and($copy->title())->toBe($page->title())
            ->and($copy->metaDescription())->toBe($page->metaDescription())
            ->and($copy->keyFacts())->toBe($page->keyFacts())
            ->and($copy->faq())->toBe($page->faq());
    });
});

describe('links to the sales tax state pages', function () {
    it('lists all 46 state pages on the hub', function () {
        $html = $this->get('/sales-tax-registration')->assertOk()->getContent();

        foreach (SalesTaxStatePage::availableStates() as $name) {
            expect($html)->toContain('href="'.url('/sales-tax-registration/'.States::slug($name)).'"')
                ->toContain("{$name} sales tax registration");
        }
    });

    it('lists all 46 state pages in the sitemap', function () {
        $locs = array_column(SitemapController::urls(), 'priority', 'loc');

        foreach (SalesTaxStatePage::availableStates() as $name) {
            $loc = url('/sales-tax-registration/'.States::slug($name));
            expect($locs)->toHaveKey($loc)
                ->and($locs[$loc])->toBe('0.7');
        }
    });

    it('links the Texas resale page and the Texas sales tax page to each other', function () {
        $this->get('/resale-certificates/texas')
            ->assertOk()
            ->assertSee('href="'.url('/sales-tax-registration/texas').'"', escape: false);

        $this->get('/sales-tax-registration/texas')
            ->assertOk()
            ->assertSee('href="'.url('/resale-certificates/texas').'"', escape: false)
            ->assertSee('href="'.url('/liens/texas').'"', escape: false);
    });

    it('keeps the Alaska resale page pointing at the hub', function () {
        $this->get('/resale-certificates/alaska')
            ->assertOk()
            ->assertDontSee('/sales-tax-registration/alaska', escape: false);
    });

    it('links bordering states with descriptive anchors', function () {
        $this->get('/sales-tax-registration/texas')
            ->assertOk()
            ->assertSee('Oklahoma sales tax registration', escape: false)
            ->assertSee('href="'.url('/sales-tax-registration/louisiana').'"', escape: false);
    });
});
