<?php

use App\Domains\Lien\Seo\DeadlineRulesExport;
use App\Http\Controllers\SitemapController;
use App\Support\Seo\States;

/*
 * The free lien deadline calculator (EREG-13): embedded on every state page
 * and on its own page. The date contract is in
 * tests/Feature/Lien/DeadlineCalculatorContractTest.php.
 */

/** The rule JSON a page inlines for the calculator. */
function inlinedCalculatorRules(string $html): array
{
    preg_match('/<script type="application\/json" id="lien-deadline-rules">(.*?)<\/script>/s', $html, $match);
    expect($match)->not->toBeEmpty('no inlined calculator rules');

    return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
}

it('embeds the calculator on the state page with that state fixed, above the deadline table', function () {
    $html = $this->get('/liens/texas')->assertOk()->getContent();

    expect($html)->toContain("x-data=\"lienDeadlineCalculator({ source: 'lien-deadline-rules', state: 'TX' })\"")
        ->toContain('Calculate your Texas deadlines')
        ->not->toContain('id="ldc-state"')
        ->toContain('href="'.route('liens.deadline-calculator').'"');

    expect(strpos($html, 'data-lien-deadline-calculator'))->toBeLessThan(strpos($html, 'Mechanics lien filing deadline'));

    $rules = inlinedCalculatorRules($html);
    expect(array_keys($rules))->toBe(['TX'])
        ->and($rules['TX'])->toBe(DeadlineRulesExport::forState('TX'));
});

it('computes for attorney-referral states and says so', function () {
    foreach (['DE', 'HI', 'MD'] as $code) {
        $html = $this->get('/liens/'.States::slug(States::name($code)))->assertOk()->getContent();
        $rules = inlinedCalculatorRules($html)[$code];

        expect($rules['attorney_referral'])->toBeTrue()
            ->and(collect($rules['rules'])->where('doc', 'mechanics_lien'))->not->toBeEmpty();
    }

    expect(DeadlineRulesExport::forState('TX')['attorney_referral'])->toBeFalse();
    $this->get('/liens/maryland')->assertSee('liens are filed through the courts by an attorney', escape: false);
});

it('renders the standalone calculator with every state, a FAQ and the disclaimer', function () {
    $html = $this->get('/liens/deadline-calculator')->assertOk()->getContent();

    preg_match('/<title>(.*?)<\/title>/s', $html, $title);
    expect(trim($title[1]))->toBe('Mechanics Lien Deadline Calculator')
        ->and($html)->toContain('id="ldc-state"')
        ->toContain('"@type":"FAQPage"')
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('"@type":"Service"')
        ->not->toContain('"@type":"Offer"')
        ->toContain('Confirm with counsel before relying on them.');

    $rules = inlinedCalculatorRules($html);
    expect(array_keys($rules))->toEqualCanonicalizing(array_keys(States::names()));

    foreach (States::names() as $name) {
        expect($html)->toContain('href="'.route('liens.state', ['state' => States::slug($name)]).'"');
    }

    // Inlined for all 50 states only while it stays small on the wire.
    preg_match('/<script type="application\/json" id="lien-deadline-rules">(.*?)<\/script>/s', $html, $blob);
    expect(strlen(gzencode($blob[1], 6)))->toBeLessThan(60 * 1024);
});

it('lists the calculator in the sitemap and links it from the lien hub and the lien tool boxes', function () {
    $entry = collect(SitemapController::urls())->firstWhere('loc', url('/liens/deadline-calculator'));
    expect($entry)->not->toBeNull()
        ->and($entry['priority'])->toBe('0.8');

    $link = 'href="'.route('liens.deadline-calculator').'"';
    expect($this->get('/liens')->assertOk()->getContent())->toContain($link)
        ->and($this->get('/liens/preliminary-notice')->assertOk()->getContent())->toContain($link);
});

it('caches the rules under a versioned key next to the page cache', function () {
    expect(DeadlineRulesExport::cacheKey('tx'))->toBe('seo.lien-deadline-rules.v1.TX');
});
