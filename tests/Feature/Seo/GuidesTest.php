<?php

use App\Domains\Lien\Seo\LienStatePage;
use App\Domains\SalesTax\Seo\SalesTaxStatePage;
use App\Http\Controllers\SitemapController;
use App\Support\Seo\Guides;
use App\Support\Seo\Urls;

/*
 * The /guides hub, the article template, the three data-driven guides and
 * the eleven written guides (SEO Phase E, EREG-14). Author and publisher are
 * the Organization node. Every guide in the registry, present and future, is
 * held to the same contract by the 'guides' dataset.
 */

/** The guides built from the state pages' data; every other guide is written. */
const DATA_GUIDE_SLUGS = [
    'mechanics-lien-deadlines-by-state',
    'preliminary-notice-requirements-by-state',
    'economic-nexus-thresholds-by-state',
];

// Every registry slug. The registry builds its related links with route(), so
// the dataset boots the application once to read it, skipping the exception
// bootstrapper so PHPUnit's error handlers stay untouched.
dataset('guides', function () {
    $app = require __DIR__.'/../../../bootstrap/app.php';
    $app->bootstrapWith([
        Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        Illuminate\Foundation\Bootstrap\BootProviders::class,
    ]);

    return array_keys(Guides::all());
});

/** The rendered <main> element's HTML. */
function guideMain(string $html): string
{
    preg_match('/<main>(.*)<\/main>/s', $html, $main);

    return $main[1] ?? '';
}

/** The visible words in a chunk of HTML (scripts and styles removed). */
function guideWordCount(string $html): int
{
    $text = html_entity_decode(strip_tags(preg_replace('/<(script|style)\b.*?<\/\1>/si', ' ', $html)), ENT_QUOTES);

    return count(array_filter(preg_split('/\s+/u', $text), fn (string $word) => preg_match('/[\p{L}\p{N}]/u', $word) === 1));
}

/**
 * The internal hrefs in a chunk of HTML as paths: relative links and links to
 * the app's own URL. Anchors, mailto and other hosts are left out.
 *
 * @return array<int, string>
 */
function guideInternalPaths(string $html): array
{
    preg_match_all('/href="([^"]+)"/', $html, $hrefs);
    $origin = rtrim(url('/'), '/');

    $paths = [];
    foreach ($hrefs[1] as $href) {
        $href = html_entity_decode($href, ENT_QUOTES);
        if (str_starts_with($href, $origin.'/') || $href === $origin) {
            $href = substr($href, strlen($origin)) ?: '/';
        }
        if (! str_starts_with($href, '/') || str_starts_with($href, '//')) {
            continue;
        }
        $paths[] = strtok($href, '#');
    }

    return array_values(array_unique($paths));
}

/** @return array<int, array<string, mixed>> every JSON-LD block on the page */
function guideJsonLd(string $html): array
{
    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);

    return array_map(fn (string $json) => json_decode($json, true), $blocks[1]);
}

/** @return array<string, mixed>|null the first JSON-LD block of the given @type */
function guideSchema(string $html, string $type): ?array
{
    return collect(guideJsonLd($html))->first(fn ($block) => ($block['@type'] ?? null) === $type);
}

/** The <table id="..."> element's HTML. */
function guideTable(string $html, string $id): string
{
    preg_match('/<table\b[^>]*\bid="'.preg_quote($id, '/').'".*?<\/table>/s', $html, $table);

    return $table[0] ?? '';
}

/** The <tr> in the table whose row header names the state. */
function guideRow(string $table, string $state): string
{
    preg_match_all('/<tr>.*?<\/tr>/s', $table, $rows);

    return collect($rows[0])->first(fn (string $row) => str_contains($row, '>'.$state.'</a>')) ?? '';
}

it('renders the hub with its H1, breadcrumbs, all four clusters, a card per guide and the footer link', function () {
    $html = $this->get('/guides')->assertOk()->getContent();

    preg_match('/<title>(.*?)<\/title>/s', $html, $title);
    preg_match('/<meta name="description"\s+content="([^"]*)"/', $html, $description);

    expect(html_entity_decode($title[1], ENT_QUOTES))->toBe('Guides to Liens, Lien Waivers and Sales Tax')
        ->and(mb_strlen(html_entity_decode($title[1], ENT_QUOTES)))->toBeLessThanOrEqual(60)
        ->and(mb_strlen(html_entity_decode($description[1], ENT_QUOTES)))->toBeGreaterThanOrEqual(70)->toBeLessThanOrEqual(165)
        ->and(preg_match_all('/<h1[\s>]/', $html))->toBe(1)
        ->and(preg_match('/<h1[^>]*>\s*Guides\s*<\/h1>/', $html))->toBe(1)
        ->and($html)->toContain('<nav aria-label="Breadcrumb"')
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('<link rel="canonical" href="'.Urls::absolute('/guides').'" />')
        ->toContain('>Mechanics liens</h2>')
        ->toContain('>Lien waivers</h2>')
        ->toContain('>Sales tax</h2>')
        ->toContain('>Resale certificates</h2>');

    // Fourteen guides, one card each.
    preg_match_all('#<a href="'.preg_quote(url('/guides').'/', '#').'([a-z0-9-]+)"#', $html, $cards);
    $slugs = array_keys(Guides::all());
    sort($slugs);
    $linked = $cards[1];
    sort($linked);
    expect(Guides::byCluster())->toHaveCount(4)
        ->and($cards[1])->toHaveCount(14)
        ->and($linked)->toBe($slugs);

    foreach (Guides::all() as $slug => $guide) {
        expect($html)->toContain('href="'.route('guides.show', ['slug' => $slug]).'"')
            ->toContain(e($guide['title']))
            ->toContain(e($guide['summary']))
            ->toContain('Updated <time datetime="'.$guide['updated'].'">'.Guides::displayDate($guide['updated']).'</time>');
    }

    preg_match('/<footer\b.*<\/footer>/s', $html, $footer);
    expect($footer[0])->toContain('href="'.route('guides.index').'"');
});

it('renders each guide with its title, description, canonical, article markup and FAQ', function (string $slug) {
    $guide = Guides::find($slug);
    $html = $this->get('/guides/'.$slug)->assertOk()->getContent();

    preg_match('/<title>(.*?)<\/title>/s', $html, $title);
    $title = html_entity_decode(trim($title[1]), ENT_QUOTES);
    preg_match('/<meta name="description"\s+content="([^"]*)"/', $html, $description);
    $description = html_entity_decode($description[1], ENT_QUOTES);

    expect($title)->toBe($guide['page_title'])
        ->and(mb_strlen($title))->toBeLessThanOrEqual(60)
        ->and($title)->not->toEndWith('| eRegister')
        ->and(mb_strlen($description))->toBeGreaterThanOrEqual(70)->toBeLessThanOrEqual(165)
        ->and(preg_match_all('/<h1[\s>]/', $html))->toBe(1)
        ->and(preg_match('/<h1[^>]*>\s*'.preg_quote(e($guide['title']), '/').'\s*<\/h1>/', $html))->toBe(1)
        ->and($html)->toContain('<link rel="canonical" href="'.Guides::url($slug).'" />')
        ->toContain('<meta property="og:type" content="article" />')
        ->toContain('Published <time datetime="'.$guide['published'].'">')
        ->toContain('<span aria-current="page" class="font-medium">'.e($guide['title']).'</span>');

    $organization = Urls::absolute('/').'#organization';
    $article = guideSchema($html, 'Article');
    expect($article)->not->toBeNull()
        ->and($article['headline'])->toBe($guide['title'])
        ->and($article['author']['@id'])->toBe($organization)
        ->and($article['publisher']['@id'])->toBe($organization)
        ->and($article['datePublished'])->toBe($guide['published'])
        ->and($article['dateModified'])->toBe($guide['updated'])
        ->and($article['mainEntityOfPage'])->toBe(Guides::url($slug))
        ->and(guideSchema($html, 'Organization')['@id'])->toBe($organization);

    $breadcrumbs = guideSchema($html, 'BreadcrumbList');
    expect($breadcrumbs)->not->toBeNull()
        ->and(array_column($breadcrumbs['itemListElement'], 'name'))->toBe(['Home', 'Guides', $guide['title']]);

    $faq = guideSchema($html, 'FAQPage');
    expect($faq)->not->toBeNull()
        ->and(count($faq['mainEntity']))->toBeGreaterThanOrEqual(4);

    // A Sources section with primary-source links that pass link equity. The
    // written guides cite at least five; the data guides list a statute or
    // agency page per state.
    preg_match('/>Sources<\/h2>(.*?)<\/ol>/s', $html, $sources);
    expect($sources)->not->toBeEmpty()
        ->and(substr_count($html, 'nofollow'))->toBe(0);
    if (! in_array($slug, DATA_GUIDE_SLUGS, true)) {
        expect(preg_match_all('/<a href="https?:\/\//', $sources[1]))->toBeGreaterThanOrEqual(5);
    }

    // The article (below its header) links to at least five pages on the site.
    preg_match('/<\/header>(.*?)<\/article>/s', $html, $article);
    expect(count(guideInternalPaths($article[1])))->toBeGreaterThanOrEqual(5);

    // No Markdown or template leftovers, and a real article's worth of words.
    expect($html)->not->toContain('{{price:')
        ->not->toContain('**')
        ->not->toContain('](')
        ->and(guideWordCount(guideMain($html)))->toBeGreaterThanOrEqual(900);
})->with('guides');

it('links only to internal pages that resolve', function (string $slug) {
    $html = $this->get('/guides/'.$slug)->assertOk()->getContent();

    // Links only: the head's <link href="/img/..."> files are static assets.
    preg_match_all('/<a\b[^>]*\bhref="(\/(?!\/)[^"#]*)/', $html, $hrefs);
    $paths = array_values(array_unique(array_map(fn (string $href) => html_entity_decode($href, ENT_QUOTES), $hrefs[1])));

    // The data guides link with absolute route() URLs; the written guides use paths.
    expect($paths)->toBeArray();
    if (! in_array($slug, DATA_GUIDE_SLUGS, true)) {
        expect(count($paths))->toBeGreaterThanOrEqual(5);
    }

    foreach ($paths as $path) {
        expect($this->get($path)->getStatusCode())->toBeIn([200, 301], $path);
    }
})->with('guides');

it('lists all 50 states in the lien deadlines table with the state pages\' headline deadline', function () {
    $html = $this->get('/guides/mechanics-lien-deadlines-by-state')->assertOk()->getContent();
    $table = guideTable($html, 'lien-deadlines-table');

    preg_match_all('#href="'.preg_quote(url('/liens').'/', '#').'([a-z-]+)"#', $table, $links);
    expect(array_unique($links[1]))->toHaveCount(50);

    $texas = LienStatePage::forCode('TX');
    $row = guideRow($table, 'Texas');
    expect($row)->toContain('<td class="min-w-56 text-zinc-900">'.e(ucfirst($texas->headlineLienDeadline())).'</td>');

    // The attorney-filing states carry the footnote marker.
    foreach (['Delaware', 'Hawaii', 'Maryland'] as $state) {
        expect(guideRow($table, $state))->toContain('href="#attorney-filing"');
    }
    expect(guideRow($table, 'Texas'))->not->toContain('#attorney-filing')
        ->and($html)->toContain('id="attorney-filing"')
        ->toContain('href="'.route('liens.deadline-calculator').'"')
        ->toContain('Confirm with counsel');
});

it('lists all 50 states in the preliminary notice table', function () {
    $html = $this->get('/guides/preliminary-notice-requirements-by-state')->assertOk()->getContent();
    $table = guideTable($html, 'prelim-notice-table');

    preg_match('/<tbody\b.*?<\/tbody>/s', $table, $body);
    expect(substr_count($body[0], '<tr>'))->toBe(50);

    $california = LienStatePage::forCode('CA');
    expect(guideRow($table, 'California'))->toContain(e($california->prelimSummary()))
        ->toContain(e(ucfirst($california->prelimRecipientsLabel())));
    expect(guideRow($table, 'Alabama'))->toContain('No preliminary notice');
    expect($html)->toContain('href="'.route('liens.preliminary-notice').'"')
        ->toContain('Confirm with counsel');
});

it('lists every sales tax state in the nexus table with the state pages\' threshold', function () {
    $html = $this->get('/guides/economic-nexus-thresholds-by-state')->assertOk()->getContent();
    $table = guideTable($html, 'nexus-thresholds-table');

    preg_match('/<tbody\b.*?<\/tbody>/s', $table, $body);
    expect(substr_count($body[0], '<tr>'))->toBe(46)
        ->and(count(SalesTaxStatePage::availableStates()))->toBe(46);

    $texas = SalesTaxStatePage::forCode('TX');
    expect(guideRow($table, 'Texas'))->toContain(e(ucfirst($texas->thresholdPhrase())))
        ->toContain(e($texas->agencyName()));

    foreach (['alaska', 'delaware', 'montana', 'new-hampshire', 'oregon'] as $slug) {
        expect($html)->toContain('href="'.route('resale-certificates.state', ['state' => $slug]).'"');
    }
    expect($html)->toContain('https://www.supremecourt.gov/opinions/17pdf/17-494_j4el.pdf')
        ->toContain('href="'.route('sales-tax-registration').'"')
        ->not->toContain('Confirm with counsel');
});

it('404s an unknown guide', function () {
    $this->get('/guides/not-a-guide')->assertNotFound();
});

it('lists the hub and every guide in the sitemap with the registry date', function () {
    $entries = collect(SitemapController::urls())->keyBy('loc');

    expect($entries)->toHaveKey(Urls::absolute('/guides'))
        ->and($entries[Urls::absolute('/guides')]['priority'])->toBe('0.7')
        ->and($entries[Urls::absolute('/guides')]['changefreq'])->toBe('weekly')
        ->and($entries[Urls::absolute('/guides')]['lastmod'] >= Guides::lastUpdated())->toBeTrue();

    $guideUrls = $entries->keys()->filter(fn (string $loc) => str_starts_with($loc, Urls::absolute('/guides/')));
    expect($guideUrls)->toHaveCount(14);

    foreach (Guides::all() as $slug => $guide) {
        $entry = $entries[Guides::url($slug)] ?? null;
        expect($entry)->not->toBeNull()
            ->and($entry['priority'])->toBe('0.6')
            ->and($entry['changefreq'])->toBe('monthly')
            ->and($entry['lastmod'])->toBe($guide['updated']);
    }
});

it('links the guides from the pages they extend', function () {
    $deadlines = route('guides.show', ['slug' => 'mechanics-lien-deadlines-by-state']);

    $this->get('/liens')->assertOk()->assertSee('href="'.$deadlines.'"', false);
    $this->get('/liens/deadline-calculator')->assertOk()->assertSee('href="'.$deadlines.'"', false);
    $this->get('/liens/preliminary-notice')->assertOk()
        ->assertSee('href="'.route('guides.show', ['slug' => 'preliminary-notice-requirements-by-state']).'"', false);
    $this->get('/sales-tax-registration')->assertOk()
        ->assertSee('href="'.route('guides.show', ['slug' => 'economic-nexus-thresholds-by-state']).'"', false);

    // The written guides, from the product page each one supports.
    $guide = fn (string $slug) => 'href="'.route('guides.show', ['slug' => $slug]).'"';
    $this->get('/liens')->assertSee($guide('how-to-file-a-mechanics-lien'), false);
    $this->get('/liens/lien-waivers')->assertOk()
        ->assertSee($guide('conditional-vs-unconditional-lien-waivers'), false)
        ->assertSee($guide('how-to-fill-out-a-lien-waiver'), false);
    $this->get('/liens/notice-of-intent-to-lien')->assertOk()->assertSee($guide('notice-of-intent-to-lien-explained'), false);
    $this->get('/liens/lien-release')->assertOk()->assertSee($guide('how-to-release-a-mechanics-lien'), false);
    $this->get('/liens/payment-demand-letter')->assertOk()->assertSee($guide('what-to-do-when-a-contractor-or-owner-doesnt-pay'), false);
    $this->get('/resale-certificates')->assertOk()->assertSee($guide('sellers-permit-vs-resale-certificate'), false);
    $this->get('/resale-certificates/texas')->assertOk()->assertSee($guide('how-to-fill-out-texas-form-01-339'), false);
    $this->get('/resale-certificates/california')->assertOk()->assertDontSee($guide('how-to-fill-out-texas-form-01-339'), false);
    $this->get('/sales-tax-registration')
        ->assertSee($guide('when-do-you-need-a-sales-tax-permit'), false)
        ->assertSee($guide('sales-tax-registration-checklist'), false);
});

it('keeps every registry entry within the title and description budgets', function () {
    foreach (Guides::all() as $guide) {
        expect(mb_strlen($guide['page_title']))->toBeLessThanOrEqual(60)
            ->and(mb_strlen($guide['description']))->toBeGreaterThanOrEqual(70)->toBeLessThanOrEqual(165)
            ->and(array_keys(Guides::CLUSTERS))->toContain($guide['cluster'])
            ->and(view()->exists($guide['view']))->toBeTrue();
    }
});

it('publishes no email, empty anchor, street address or raw field name on a guide', function (string $slug) {
    $html = $this->get('/guides/'.$slug)->assertOk()->getContent();
    $text = strip_tags(preg_replace('/<(script|style)\b.*?<\/\1>/si', ' ', $html));

    expect($html)->not->toContain('href="#"')
        ->not->toContain('mailto:')
        ->not->toContain('Brownsboro')
        ->and(preg_match('/[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}/', $html))->toBe(0)
        ->and(preg_match('/\b\w+_(?:date|logic|for|recipients|method|days|months|trigger|required)\b/', $text, $match))->toBe(0, $match[0] ?? '')
        ->and($text)->not->toContain('subsubs')
        ->not->toContain('owner_gc')
        ->not->toContain('certified_mail');

    foreach (['lien_anchor_logic', 'enforcement_trigger', 'first_furnish_date', 'pre_notice_required', 'noi_lead_time_days', 'revenue_usd'] as $field) {
        expect($html)->not->toContain($field);
    }
})->with('guides');
