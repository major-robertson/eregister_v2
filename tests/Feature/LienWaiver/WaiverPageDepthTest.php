<?php

use App\Domains\Lien\Waivers\WaiverBlankForms;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Storage;

/*
 * Depth on the lien waiver state pages and hub (EREG-13): the reviewed
 * ui_notes, a data-built FAQ, free Service schema, blank statutory PDFs,
 * and the generator-vs-template section.
 */

/** @return list<array<string, mixed>> */
function waiverJsonLd(string $html): array
{
    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);

    return array_map(fn (string $json) => json_decode($json, true), $blocks[1]);
}

/** @return array<string, mixed>|null */
function waiverJsonLdOfType(string $html, string $type): ?array
{
    foreach (waiverJsonLd($html) as $block) {
        if (($block['@type'] ?? null) === $type) {
            return $block;
        }
    }

    return null;
}

beforeEach(function () {
    Storage::fake('s3');
    Storage::fake('local');
});

it('renders the Texas notes, FAQ, free Service schema and blank-form links', function () {
    $html = $this->get('/liens/lien-waivers/tx')
        ->assertOk()
        ->assertSee('What to know about Texas lien waivers')
        ->assertSee(WaiverStateRegistry::for('TX')['ui_notes'][0])
        ->assertSee(WaiverStateRegistry::for('TX')['ui_notes'][1])
        ->assertSee('Download the blank Texas forms')
        ->getContent();

    $start = strpos($html, 'Texas lien waiver questions');
    $faqSection = substr($html, $start, strpos($html, '</section>', $start) - $start);

    $faq = waiverJsonLdOfType($html, 'FAQPage');
    expect($faq)->not->toBeNull()
        ->and($faq['mainEntity'])->toHaveCount(5)
        ->and(substr_count($faqSection, '<details'))->toBe(5);

    // The advance-waiver answer quotes the data file.
    $answers = array_column(array_column($faq['mainEntity'], 'acceptedAnswer'), 'text');
    expect($answers)->toContain(WaiverStateRegistry::for('TX')['advance_waiver_note']);

    $service = waiverJsonLdOfType($html, 'Service');
    expect($service['name'])->toBe('Texas Lien Waiver Generator')
        ->and($service['serviceType'])->toBe('Lien waiver forms')
        ->and($service['offers']['@type'])->toBe('Offer')
        ->and($service['offers']['price'])->toBe('0.00')
        ->and($service['offers']['priceCurrency'])->toBe('USD');

    foreach (['conditional-progress', 'unconditional-progress', 'conditional-final', 'unconditional-final'] as $kind) {
        expect($html)->toContain(route('liens.lien-waivers.blank', ['state' => 'tx', 'kind' => $kind]));
    }
});

it('skips the notes section for a state without notes and offers the generator instead of blanks', function () {
    expect(WaiverStateRegistry::for('HI')['ui_notes'])->toBe([]);

    $this->get('/liens/lien-waivers/hi')
        ->assertOk()
        ->assertDontSee('What to know about Hawaii lien waivers')
        ->assertDontSee('Download the blank Hawaii forms')
        ->assertSee('Hawaii has no statutory waiver form to download blank', false)
        ->assertDontSee('/blank/', false);
});

it('leaves out FAQ questions the data cannot answer', function () {
    // Iowa has no advance-waiver note, so that question is omitted.
    expect(WaiverStateRegistry::for('IA')['advance_waiver_note'])->toBeNull();

    $faq = waiverJsonLdOfType($this->get('/liens/lien-waivers/ia')->assertOk()->getContent(), 'FAQPage');

    expect($faq['mainEntity'])->toHaveCount(4)
        ->and(array_column($faq['mainEntity'], 'name'))->not->toContain('Is a lien waiver signed in advance enforceable in Iowa?');
});

it('answers Georgia and Wyoming from their own kinds and execution rules', function () {
    $ga = waiverJsonLdOfType($this->get('/liens/lien-waivers/ga')->getContent(), 'FAQPage');
    $gaAnswers = implode(' ', array_column(array_column($ga['mainEntity'], 'acceptedAnswer'), 'text'));
    expect($gaAnswers)->toContain('Georgia prescribes two statutory lien waiver forms in O.C.G.A. § 44-14-366')
        ->toContain('must be signed before a witness')
        ->toContain('Georgia has no unconditional waiver');

    $wy = waiverJsonLdOfType($this->get('/liens/lien-waivers/wy')->getContent(), 'FAQPage');
    $wyAnswers = implode(' ', array_column(array_column($wy['mainEntity'], 'acceptedAnswer'), 'text'));
    expect($wyAnswers)->toContain('Wyoming prescribes one statutory lien waiver form')
        ->toContain('Yes. Wyoming lien waivers must be signed before a notary.')
        ->toContain('Wyoming uses one form, the Lien Waiver, for both.');
});

it('serves the blank Texas conditional progress waiver as a cached PDF attachment', function () {
    $response = $this->get('/liens/lien-waivers/tx/blank/conditional-progress.pdf')->assertOk();

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toBe('attachment; filename="texas-conditional-progress-lien-waiver-blank.pdf"')
        ->and(substr($response->getContent(), 0, 4))->toBe('%PDF');

    $files = Storage::disk('local')->allFiles(WaiverBlankForms::CACHE_DIRECTORY);
    expect($files)->toHaveCount(1)
        ->and($files[0])->toStartWith(WaiverBlankForms::CACHE_DIRECTORY.'/v'.WaiverBlankForms::CACHE_VERSION.'/tx-conditional-progress-t1-');

    // The second request is served from the cached file.
    Storage::disk('local')->put($files[0], '%PDF-cached');
    expect($this->get('/liens/lien-waivers/tx/blank/conditional-progress.pdf')->getContent())->toBe('%PDF-cached');
});

it('404s blank forms for unknown kinds, unused kinds and non-statutory states', function () {
    $this->get('/liens/lien-waivers/tx/blank/bogus.pdf')->assertNotFound();
    $this->get('/liens/lien-waivers/oh/blank/conditional-progress.pdf')->assertNotFound();
    $this->get('/liens/lien-waivers/ma/blank/conditional-progress.pdf')->assertNotFound();
    // Georgia has no unconditional waiver; Missouri's only statutory form is the final one.
    $this->get('/liens/lien-waivers/ga/blank/unconditional-progress.pdf')->assertNotFound();
    $this->get('/liens/lien-waivers/mo/blank/conditional-progress.pdf')->assertNotFound();
    $this->get('/liens/lien-waivers/zz/blank/conditional-progress.pdf')->assertNotFound();

    $this->get('/liens/lien-waivers/TX/blank/conditional-progress.pdf')
        ->assertStatus(301)
        ->assertRedirect(route('liens.lien-waivers.blank', ['state' => 'tx', 'kind' => 'conditional-progress']));
});

it('renders every listed blank form as a PDF', function () {
    $blanks = app(WaiverBlankForms::class)->all();

    expect(array_keys($blanks))->toBe(['AZ', 'CA', 'FL', 'GA', 'MI', 'MS', 'MO', 'NV', 'TX', 'UT', 'WY'])
        ->and(array_keys($blanks['MO']))->toBe(['unconditional-final'])
        ->and(array_keys($blanks['WY']))->toBe(['unconditional-progress']);

    foreach ($blanks as $code => $forms) {
        foreach (array_keys($forms) as $slug) {
            $response = $this->get(route('liens.lien-waivers.blank', ['state' => strtolower($code), 'kind' => $slug]))->assertOk();
            expect(substr($response->getContent(), 0, 4))->toBe('%PDF', "{$code} {$slug}");
        }
    }
});

it('lists blank forms, the comparison section and free Service schema on the hub', function () {
    $html = $this->get('/liens/lien-waivers')
        ->assertOk()
        ->assertSee('Blank forms by state')
        ->assertSee('Why a generator beats a Word template')
        ->assertSee(\App\Support\Seo\Prices::waiver().' a month per person')
        ->assertSee('Texas lien waiver forms')
        ->getContent();

    expect($html)->toContain(route('liens.lien-waivers.blank', ['state' => 'tx', 'kind' => 'conditional-progress']))
        ->toContain(route('liens.lien-waivers.blank', ['state' => 'mo', 'kind' => 'unconditional-final']))
        ->not->toContain(route('liens.lien-waivers.blank', ['state' => 'oh', 'kind' => 'conditional-progress']));

    $service = waiverJsonLdOfType($html, 'Service');
    expect($service['name'])->toBe('Lien Waiver Generator')
        ->and($service['offers']['price'])->toBe('0.00');
});

it('keeps every state page within the sitemap contract and the PDFs out of the sitemap', function () {
    foreach (array_keys(WaiverStateRegistry::STATE_NAMES) as $code) {
        $html = $this->get('/liens/lien-waivers/'.strtolower($code))->assertOk()->getContent();

        preg_match('/<title>(.*?)<\/title>/s', $html, $title);
        preg_match('/<meta name="description"\s+content="([^"]*)"/', $html, $description);
        $title = html_entity_decode(trim($title[1] ?? ''), ENT_QUOTES);
        $description = html_entity_decode($description[1] ?? '', ENT_QUOTES);

        expect(mb_strlen($title))->toBeLessThanOrEqual(70, "{$code}: {$title}")
            ->and(mb_strlen($description))->toBeGreaterThanOrEqual(70, "{$code}: {$description}")
            ->toBeLessThanOrEqual(165, "{$code}: {$description}")
            ->and(preg_match_all('/<h1[\s>]/', $html))->toBe(1, "{$code}: h1 count")
            ->and($html)->not->toContain('href="#"');

        $faq = waiverJsonLdOfType($html, 'FAQPage');
        expect(count($faq['mainEntity'] ?? []))->toBeGreaterThanOrEqual(4, $code)->toBeLessThanOrEqual(5, $code);
    }

    $locs = array_column(SitemapController::urls(), 'loc');
    expect(array_filter($locs, fn (string $loc) => str_contains($loc, '/blank/')))->toBe([]);
});
