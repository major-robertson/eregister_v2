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
