<?php

use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Seo\BlankLienClaim;
use App\Domains\Lien\Seo\LienStateDepth;
use App\Domains\Lien\Seo\LienStatePage;
use App\Http\Controllers\SitemapController;
use App\Support\Seo\States;
use Illuminate\Support\Facades\Storage;

/*
 * Depth on the mechanics lien state pages of the ten busiest states
 * (EREG-13): how to file, what the claim must contain, the county offices,
 * recent law changes, six more FAQs, and a blank lien claim PDF rendered
 * through the filing product's own instrument pipeline.
 */

/** @return array<string, mixed>|null */
function lienDepthJsonLdOfType(string $html, string $type): ?array
{
    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);

    foreach ($blocks[1] as $json) {
        $block = json_decode($json, true);
        if (($block['@type'] ?? null) === $type) {
            return $block;
        }
    }

    return null;
}

function lienDepthVisibleText(string $html): string
{
    preg_match('/<main\b[^>]*>(.*)<\/main>/s', $html, $main);
    $body = $main[1] ?? $html;
    $text = strip_tags(preg_replace('/<(script|style)\b.*?<\/\1>/si', ' ', $body));

    return trim(preg_replace('/\s+/', ' ', html_entity_decode($text, ENT_QUOTES)));
}

beforeEach(function () {
    Storage::fake('s3');
    Storage::fake('local');
    LienStateDepth::flush();
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

it('loads a depth file for each of the ten popular states and none for the rest', function () {
    expect(array_keys(LienStateDepth::popular()))->toBe(LienStateDepth::POPULAR)
        ->and(LienStateDepth::exists('VT'))->toBeFalse()
        ->and(LienStatePage::forCode('VT')->depth)->toBeNull();

    foreach (LienStateDepth::POPULAR as $code) {
        $depth = LienStateDepth::for($code);

        expect($depth['state'])->toBe($code)
            ->and(count($depth['how_to_file']))->toBeGreaterThanOrEqual(6, $code)
            ->and($depth['claim_contents']['required'])->not->toBeEmpty()
            ->and($depth['counties'])->toHaveCount(10)
            ->and($depth['faqs'])->toHaveCount(6)
            ->and($depth)->not->toHaveKey('unconfirmed');
    }
});

it('renders the Texas steps, claim contents, county offices, recent changes and twelve FAQs', function () {
    $html = $this->get('/liens/texas')->assertOk()->getContent();

    expect($html)
        ->toContain('How to file a Texas mechanics lien')
        ->toContain('Send a copy of the filed affidavit within 5 days')
        ->toContain('Residential projects:')
        ->toContain('What the affidavit claiming a mechanic&#039;s lien must contain')
        ->toContain('A sworn statement of the amount of the claim')
        ->toContain('Where to record in Texas')
        ->toContain('Harris County Clerk, Real Property')
        ->toContain('Dallas County Clerk, Recording Division')
        ->toContain('only from the submitters listed in Local Gov&#039;t Code § 195.003')
        ->toContain('Fee not published online; ask the office.')
        ->toContain('Recent changes to Texas lien law')
        ->toContain('HB 2237')
        ->toContain('Download a blank Texas lien claim form')
        ->toContain(route('liens.state.blank-claim', ['state' => 'texas']));

    // Sections sit between the deadline table and "Recording and enforcing",
    // and the county table and changes follow it.
    $deadlines = strpos($html, 'id="deadlines"');
    $howTo = strpos($html, 'id="how-to-file"');
    $contents = strpos($html, 'id="claim-contents"');
    $recording = strpos($html, 'Recording and enforcing');
    $counties = strpos($html, 'id="county-offices"');
    $changes = strpos($html, 'id="recent-changes"');
    expect($deadlines)->toBeLessThan($howTo)
        ->and($howTo)->toBeLessThan($contents)
        ->and($contents)->toBeLessThan($recording)
        ->and($recording)->toBeLessThan($counties)
        ->and($counties)->toBeLessThan($changes);

    $faq = lienDepthJsonLdOfType($html, 'FAQPage');
    $questions = array_column($faq['mainEntity'], 'name');
    expect($questions)->toHaveCount(12)
        ->toContain('Can I file a lien on a Texas public project?')
        ->and(preg_match_all('/<details class="group rounded-2xl/', $html))->toBe(12);

    expect(str_word_count(lienDepthVisibleText($html)))->toBeGreaterThanOrEqual(1400);
});

it('renders the Florida and Georgia county intros and Pennsylvania as filed', function () {
    $this->get('/liens/florida')->assertOk()
        ->assertSee('In Broward County the Records, Taxes and Treasury Division does', false)
        ->assertSee('Where to record in Florida', false)
        ->assertSee('See the official Florida form wording', false);

    $georgia = $this->get('/liens/georgia')->assertOk()->getContent();
    expect($georgia)->toContain('Since January 1, 2025, anyone filing their own lien must e-file it')
        ->toContain('Where to file in Georgia')
        // The intro sits above the table.
        ->and(strpos($georgia, 'Since January 1, 2025, anyone filing'))->toBeLessThan(strpos($georgia, '<th scope="col" class="px-6 py-3 font-semibold">County</th>'));

    $this->get('/liens/pennsylvania')->assertOk()
        ->assertSee('Where to file in Pennsylvania', false)
        ->assertSee('E-filing', false)
        ->assertSee('Pending, not law', false);
});

it('leaves a state without depth exactly as before', function () {
    $html = $this->get('/liens/vermont')->assertOk()->getContent();

    expect($html)->not->toContain('id="how-to-file"')
        ->not->toContain('id="claim-contents"')
        ->not->toContain('id="county-offices"')
        ->not->toContain('id="recent-changes"')
        ->not->toContain('blank-lien-claim.pdf')
        ->and(count(lienDepthJsonLdOfType($html, 'FAQPage')['mainEntity']))->toBe(count(LienStatePage::forCode('VT')->faq()))
        ->and(count(LienStatePage::forCode('VT')->faq()))->toBeLessThanOrEqual(7);
});

it('serves the blank Texas lien claim as a cached PDF attachment', function () {
    $response = $this->get('/liens/texas/blank-lien-claim.pdf')->assertOk();

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toBe('attachment; filename="texas-affidavit-claiming-a-mechanics-lien-blank.pdf"')
        ->and(substr($response->getContent(), 0, 4))->toBe('%PDF');

    $files = Storage::disk('local')->allFiles(BlankLienClaim::CACHE_DIRECTORY);
    expect($files)->toHaveCount(1)
        ->and($files[0])->toStartWith(BlankLienClaim::CACHE_DIRECTORY.'/v'.BlankLienClaim::CACHE_VERSION.'/tx-t');

    // The second request is served from the cached file.
    Storage::disk('local')->put($files[0], '%PDF-cached');
    expect($this->get('/liens/texas/blank-lien-claim.pdf')->getContent())->toBe('%PDF-cached');
});

it('prints the blank instrument with no names, amounts or preparer', function () {
    $blanks = app(BlankLienClaim::class);
    $form = $blanks->form('TX');
    $html = view(app(\App\Domains\Lien\Documents\LienDocumentGenerator::class)->layout($form), ['doc' => $blanks->payload($form)])->render();

    expect($html)->toContain('AFFIDAVIT CLAIMING A MECHANIC')
        ->toContain('STATE OF TEXAS')
        ->not->toContain('eRegister</div>')
        ->not->toContain(now()->eastern()->format('F j, Y'));
});

it('404s the blank claim for states without an instrument and 301s codes to the slug', function () {
    $this->get('/liens/vermont/blank-lien-claim.pdf')->assertNotFound();
    $this->get('/liens/maryland/blank-lien-claim.pdf')->assertNotFound();
    $this->get('/liens/atlantis/blank-lien-claim.pdf')->assertNotFound();

    $this->get('/liens/TX/blank-lien-claim.pdf')->assertStatus(301)
        ->assertRedirect(route('liens.state.blank-claim', ['state' => 'texas']));
    $this->get('/liens/tx/blank-lien-claim.pdf')->assertStatus(301)
        ->assertRedirect(route('liens.state.blank-claim', ['state' => 'texas']));
});

it('renders a blank lien claim for every state with an instrument file', function () {
    $blanks = app(BlankLienClaim::class);
    $codes = array_keys(array_filter(States::names(), fn (string $name, string $code) => $blanks->available($code), ARRAY_FILTER_USE_BOTH));

    expect($codes)->toContain(...LienStateDepth::POPULAR)->not->toContain('VT');

    foreach ($codes as $code) {
        $pdf = $blanks->pdf($code);
        expect(substr((string) $pdf, 0, 4))->toBe('%PDF', $code);
    }
});

it('keeps the blank lien claim PDFs out of the sitemap', function () {
    $locs = array_column(SitemapController::urls(), 'loc');

    expect(array_filter($locs, fn (string $loc) => str_contains($loc, 'blank-lien-claim')))->toBe([]);
});

it('shows the popular states row above the full grid on the hub', function () {
    $html = $this->get('/liens')->assertOk()->getContent();

    $popular = strpos($html, 'Popular states');
    expect($popular)->not->toBeFalse()
        ->and($popular)->toBeLessThan(strpos($html, 'All 50 states'));

    $row = substr($html, $popular, strpos($html, 'All 50 states') - $popular);
    foreach (LienStateDepth::popular() as $name) {
        expect($row)->toContain('href="'.route('liens.state', ['state' => States::slug($name)]).'"')
            ->toContain("{$name} mechanics lien");
    }
});

it('keeps every popular state page title and description within budget', function () {
    foreach (LienStateDepth::POPULAR as $code) {
        $page = LienStatePage::forCode($code);

        // Titles are unchanged by the depth work; the sitemap contract caps them at 70.
        expect(mb_strlen($page->title()))->toBeLessThanOrEqual(70, $page->title())
            ->and(mb_strlen($page->metaDescription()))->toBeGreaterThanOrEqual(70, $page->metaDescription())
            ->toBeLessThanOrEqual(165, $page->metaDescription())
            ->and($page->extraFaqs())->toHaveCount(6)
            ->and(array_slice($page->faq(), -6))->toBe($page->extraFaqs());
    }
});
