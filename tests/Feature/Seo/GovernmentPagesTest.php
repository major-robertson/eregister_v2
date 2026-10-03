<?php

/*
 * The seven government service pages carry enough plain copy to rank
 * (700 to 1,000 visible words), one FAQ with FAQPage markup, links into the
 * rest of the government section, and no invented proof: no percentages,
 * uptime figures, client claims, certifications or federal identifiers.
 */

const GOVERNMENT_SERVICE_PAGES = ['website-redesign', 'cms', 'hosting', 'maintenance', 'portals', 'integrations', 'implementation'];

// Proof the pages must never claim, matched against the visible text of <main>.
const GOVERNMENT_FORBIDDEN_PHRASES = ['%', '99.', 'clients', 'case study', 'certified', 'SOC 2', 'FedRAMP', 'StateRAMP', 'UEI', 'CAGE', 'NAICS', 'Brownsboro', 'Louisville'];

if (! function_exists('governmentPageMain')) {
    /** The inner HTML of the page's <main> element. */
    function governmentPageMain(string $html): string
    {
        return preg_match('/<main\b[^>]*>(.*)<\/main>/s', $html, $m) ? $m[1] : '';
    }
}

if (! function_exists('governmentPageText')) {
    /** Visible text of a chunk of HTML: scripts, styles and SVGs dropped, tags stripped, entities decoded. */
    function governmentPageText(string $html): string
    {
        $html = preg_replace('/<(script|style|svg)\b[^>]*>.*?<\/\1>/s', ' ', $html);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5)));
    }
}

it('gives each government service page enough copy, an FAQ and links into the section', function (string $slug) {
    $path = "/government/{$slug}";
    $html = $this->get($path)->assertOk()->getContent();
    $main = governmentPageMain($html);
    $text = governmentPageText($main);

    expect($main)->not->toBe('', "{$path} has no <main>");
    expect(preg_match_all('/<h1[\s>]/', $html))->toBe(1, "{$path} has more or fewer than one <h1>");

    // Headings never skip a level on the way down (h2 -> h4, say).
    preg_match_all('/<h([1-6])\b/', $html, $levels);
    $levels = array_map('intval', $levels[1]);
    foreach ($levels as $i => $level) {
        if ($i > 0) {
            expect($level)->toBeLessThanOrEqual($levels[$i - 1] + 1, "{$path} skips from h{$levels[$i - 1]} to h{$level}");
        }
    }

    $words = count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
    expect($words)->toBeGreaterThanOrEqual(700, "{$path} has {$words} visible words")
        ->toBeLessThanOrEqual(1000, "{$path} has {$words} visible words");

    // Exactly one FAQPage block, with at least five questions.
    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);
    $faqs = array_values(array_filter(
        array_map(fn (string $json) => json_decode($json, true), $blocks[1]),
        fn ($data) => is_array($data) && ($data['@type'] ?? null) === 'FAQPage',
    ));
    expect($faqs)->toHaveCount(1, "{$path} should carry exactly one FAQPage block");
    expect(count($faqs[0]['mainEntity'] ?? []))->toBeGreaterThanOrEqual(5, "{$path} FAQ has fewer than five questions");

    // Links in the page body, not the header or footer.
    expect(preg_match('/href="[^"]*\/government\/capabilities"/', $main))->toBe(1, "{$path} does not link to /government/capabilities in the body");
    expect(preg_match('/href="[^"]*\/contact"/', $main))->toBe(1, "{$path} does not link to /contact in the body");

    // At least three other service pages linked from the copy itself, leaving
    // out the randomised "related services" cards.
    $body = preg_replace('/<section\b(?:(?!<section\b).)*?Often paired with this engagement.*?<\/section>/s', '', $main);
    preg_match_all('/href="[^"]*\/government\/([a-z0-9-]+)"/', $body, $links);
    $others = array_diff(array_unique($links[1]), [$slug, 'capabilities']);
    expect(count($others))->toBeGreaterThanOrEqual(3, "{$path} links to only ".count($others).' other government pages in its copy');

    foreach (GOVERNMENT_FORBIDDEN_PHRASES as $phrase) {
        expect(stripos($text, $phrase))->toBeFalse("{$path} contains \"{$phrase}\"");
    }
    expect(preg_match('/[\w.+-]+@[\w-]+\.[\w.]+/', $text))->toBe(0, "{$path} shows an email address");
    expect($html)->not->toContain('mailto:');
})->with(GOVERNMENT_SERVICE_PAGES);

/*
 * The ADA Title II guide (/government/accessibility) and the Florida and
 * North Carolina pages: long-form, sourced, one FAQ, Article markup that
 * names the Organization, and links that resolve.
 */
dataset('government content pages', [
    'accessibility' => ['/government/accessibility', 'ADA Title II Website Accessibility Deadlines', 1100],
    'florida' => ['/government/florida', 'Florida Government Website Accessibility and Procurement', 800],
    'north carolina' => ['/government/north-carolina', 'North Carolina Government Website Accessibility and Procurement', 800],
]);

it('gives each government content page its title, markup, sources and copy', function (string $path, string $h1, int $minWords) {
    $html = $this->get($path)->assertOk()->getContent();
    $main = governmentPageMain($html);
    $text = governmentPageText($main);

    expect(preg_match_all('/<h1[\s>]/', $html))->toBe(1, "{$path} has more or fewer than one <h1>");
    expect(preg_match('/<h1[^>]*>\s*'.preg_quote(e($h1), '/').'\s*<\/h1>/', $html))->toBe(1, "{$path} H1 is not \"{$h1}\"");

    preg_match('/<title>(.*?)<\/title>/s', $html, $title);
    expect(mb_strlen(html_entity_decode(trim($title[1] ?? ''), ENT_QUOTES)))->toBeGreaterThan(0)->toBeLessThanOrEqual(60);

    preg_match('/<meta name="description"\s+content="([^"]*)"/', $html, $description);
    $description = html_entity_decode($description[1] ?? '', ENT_QUOTES);
    expect(mb_strlen($description))->toBeGreaterThanOrEqual(70)->toBeLessThanOrEqual(165);

    expect($html)->toContain('<link rel="canonical" href="'.\App\Support\Seo\Urls::absolute($path).'" />');

    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);
    $schemas = array_map(fn (string $json) => json_decode($json, true), $blocks[1]);
    $ofType = fn (string $type) => array_values(array_filter($schemas, fn ($data) => is_array($data) && ($data['@type'] ?? null) === $type));

    $faqs = $ofType('FAQPage');
    expect($faqs)->toHaveCount(1, "{$path} should carry exactly one FAQPage block");
    expect(count($faqs[0]['mainEntity'] ?? []))->toBeGreaterThanOrEqual(5, "{$path} FAQ has fewer than five questions");

    $articles = $ofType('Article');
    $organization = ['@id' => \App\Support\Seo\Urls::absolute('/').'#organization'];
    expect($articles)->toHaveCount(1, "{$path} should carry exactly one Article block");
    expect($articles[0]['headline'])->toBe($h1)
        ->and($articles[0]['description'])->toBe($description)
        ->and($articles[0]['mainEntityOfPage'])->toBe(\App\Support\Seo\Urls::absolute($path))
        ->and($articles[0]['author'])->toBe($organization)
        ->and($articles[0]['publisher'])->toBe($organization)
        ->and($articles[0]['datePublished'])->toMatch('/^\d{4}-\d{2}-\d{2}$/')
        ->and($articles[0]['dateModified'])->toMatch('/^\d{4}-\d{2}-\d{2}$/')
        ->and($articles[0]['image'] ?? '')->not->toBe('');

    // A Sources section: an ordered list of followed outbound links.
    expect(preg_match('/<h2[^>]*>\s*Sources\s*<\/h2>\s*<ol\b[^>]*>(.*?)<\/ol>/s', $main, $sources))->toBe(1, "{$path} has no Sources list");
    expect(preg_match_all('/<a href="https?:\/\/[^"]+"/', $sources[1]))->toBeGreaterThanOrEqual(5, "{$path} cites fewer than five sources");

    $words = count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
    expect($words)->toBeGreaterThanOrEqual($minWords, "{$path} has {$words} visible words");

    // No Markdown left over from the drafts.
    expect($text)->not->toContain('**')->not->toContain('](')->not->toContain('| ---');

    foreach (GOVERNMENT_FORBIDDEN_PHRASES as $phrase) {
        expect(stripos($text, $phrase))->toBeFalse("{$path} contains \"{$phrase}\"");
    }
    expect(preg_match('/[\w.+-]+@[\w-]+\.[\w.]+/', $text))->toBe(0, "{$path} shows an email address");
    expect($html)->not->toContain('mailto:')->not->toContain('nofollow');
})->with('government content pages');

it('links only to internal pages that resolve', function (string $path) {
    $html = $this->get($path)->assertOk()->getContent();

    preg_match_all('/<a\b[^>]*\bhref="(\/(?!\/)[^"#]*)/', $html, $links);
    $failures = [];
    foreach (array_unique($links[1]) as $href) {
        $status = $this->get($href)->status();
        if (! in_array($status, [200, 301], true)) {
            $failures[] = "{$href} ({$status})";
        }
    }

    expect($links[1])->not->toBeEmpty("{$path} has no relative internal links")
        ->and($failures)->toBeEmpty("{$path} links to: ".implode(', ', $failures));
})->with(['/government/accessibility', '/government/florida', '/government/north-carolina']);

it('cross-links the accessibility guide and the state pages', function () {
    $guide = governmentPageMain($this->get('/government/accessibility')->getContent());
    expect($guide)->toContain('href="/government/florida"')->toContain('href="/government/north-carolina"');

    foreach (['/government/florida', '/government/north-carolina'] as $path) {
        expect(governmentPageMain($this->get($path)->getContent()))->toContain('href="/government/accessibility"');
    }
});

it('lists the guide and both state pages in the sitemap', function () {
    $entries = collect(\App\Http\Controllers\SitemapController::urls())->keyBy('loc');

    foreach (['/government/accessibility', '/government/florida', '/government/north-carolina'] as $path) {
        expect($entries)->toHaveKey(url($path))
            ->and($entries[url($path)]['priority'])->toBe('0.7')
            ->and($entries[url($path)]['changefreq'])->toBe('monthly');
    }
});

it('links both state pages from /government and from the government footer', function () {
    $home = governmentPageMain($this->get('/government')->assertOk()->getContent());
    expect($home)->toContain('href="'.route('government.florida').'"')
        ->toContain('href="'.route('government.north-carolina').'"');

    preg_match('/<footer\b.*<\/footer>/s', $this->get('/government/cms')->assertOk()->getContent(), $footer);
    expect($footer[0] ?? '')->toContain('href="'.route('government.florida').'"')
        ->toContain('href="'.route('government.north-carolina').'"');
});
