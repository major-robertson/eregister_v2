<?php

/*
 * Heading outline on the main marketing pages: one H1, levels that never
 * skip (no hero cards as <h3> straight under the <h1>), footer column labels
 * that are not <h4>, and no definition list standing in for feature cards.
 */

it('keeps a clean heading outline', function (string $path) {
    $html = $this->get($path)->assertOk()->getContent();

    preg_match_all('/<h([1-6])\b/', $html, $levels);
    $levels = array_map('intval', $levels[1]);

    expect(array_count_values($levels)[1] ?? 0)->toBe(1, "{$path} has more or fewer than one <h1>");

    $firstH2 = array_search(2, $levels, true);
    $firstH3 = array_search(3, $levels, true);
    expect($firstH2)->not->toBeFalse("{$path} has no <h2>");
    if ($firstH3 !== false) {
        expect($firstH3)->toBeGreaterThan($firstH2, "{$path} has an <h3> before its first <h2>");
    }

    // No level is skipped on the way down (h2 -> h4, say).
    foreach ($levels as $i => $level) {
        if ($i > 0) {
            expect($level)->toBeLessThanOrEqual($levels[$i - 1] + 1, "{$path} skips from h{$levels[$i - 1]} to h{$level}");
        }
    }

    preg_match('/<footer\b.*<\/footer>/s', $html, $footer);
    expect($footer[0] ?? '')->not->toBe('')->not->toContain('<h4');
})->with([
    'home' => '/',
    'liens' => '/liens',
    'llc' => '/llc',
    'government' => '/government',
    'corporation' => '/corporation',
    'registered-agent' => '/registered-agent',
    'sales-tax-registration' => '/sales-tax-registration',
    'about' => '/about',
    'government capabilities' => '/government/capabilities',
    'government accessibility' => '/government/accessibility',
    'government florida' => '/government/florida',
    'government north carolina' => '/government/north-carolina',
]);

it('builds the home page "Why eRegister" cards from headings, not a definition list', function () {
    $html = $this->get('/')->assertOk()->getContent();

    $start = strpos($html, 'Why eRegister');
    expect($start)->not->toBeFalse();
    $section = substr($html, $start, strpos($html, '</section>', $start) - $start);

    expect($section)->not->toContain('<dl')
        ->not->toContain('<dt')
        ->toContain('<h3');
});
