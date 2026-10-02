<?php

use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Seo\BlankDemandLetter;
use App\Domains\Lien\Seo\BlankLienDocument;
use App\Domains\Lien\Seo\LienReleaseStatePage;
use App\Domains\Lien\Seo\LienVariantStatePage;
use App\Domains\Lien\Seo\NoticeOfIntentStatePage;
use App\Http\Controllers\SitemapController;
use App\Support\Seo\States;
use Illuminate\Support\Facades\Storage;

/*
 * Per-state notice of intent and lien release pages for twenty states, their
 * free blank PDFs, and the free blank demand letter (EREG-13, D7).
 */

/** @return array<string, mixed>|null */
function variantJsonLdOfType(string $html, string $type): ?array
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

function variantVisibleText(string $html): string
{
    preg_match('/<main\b[^>]*>(.*)<\/main>/s', $html, $main);
    $body = $main[1] ?? $html;
    $text = strip_tags(preg_replace('/<(script|style)\b.*?<\/\1>/si', ' ', $body));

    return trim(preg_replace('/\s+/', ' ', html_entity_decode($text, ENT_QUOTES)));
}

beforeEach(function () {
    Storage::fake('s3');
    Storage::fake('local');
    LienDocumentRegistry::flush();
    LienVariantStatePage::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

it('covers the ten notice-of-intent states and the ten busiest', function () {
    expect(array_keys(NoticeOfIntentStatePage::entries()))->toEqualCanonicalizing(LienVariantStatePage::STATES)
        ->and(array_keys(LienReleaseStatePage::entries()))->toEqualCanonicalizing(LienVariantStatePage::STATES)
        ->and(NoticeOfIntentStatePage::availableStates())->toHaveCount(20)
        ->and(LienReleaseStatePage::availableStates())->toHaveCount(20);
});

it('renders Colorado as required ten days before filing, with the cite and schema', function () {
    $html = $this->get('/liens/notice-of-intent-to-lien/colorado')->assertOk()->getContent();
    $text = variantVisibleText($html);

    expect($html)->toContain('<h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">Colorado Notice of Intent to Lien</h1>')
        ->toContain('<link rel="canonical" href="'.url('/liens/notice-of-intent-to-lien/colorado').'"')
        ->and($text)->toContain('Required. Colorado requires a notice of intent before every lien statement. Serve it at least ten days before the lien statement is filed (C.R.S. § 38-22-109(3)).')
        ->toContain('At least 10 days before the lien statement is filed')
        ->toContain('registered or certified mail with return receipt requested')
        ->toContain('The Colorado lien deadline is within 4 months after last furnishing labor or materials.');

    $faq = variantJsonLdOfType($html, 'FAQPage');
    expect($faq['mainEntity'])->toHaveCount(5)
        ->and($faq['mainEntity'][0]['name'])->toBe('Is a notice of intent to lien required in Colorado?')
        ->and($faq['mainEntity'][0]['acceptedAnswer']['text'])->toContain('C.R.S. § 38-22-109(3)');

    $service = variantJsonLdOfType($html, 'Service');
    expect($service['name'])->toBe('Colorado Notice of Intent to Lien')
        ->and($service['url'])->toBe(url('/liens/notice-of-intent-to-lien/colorado'))
        ->and($service['offers']['@type'])->toBe('Offer')
        ->and($service['offers']['price'])->toBe(number_format(config('lien.pricing.noi.self_serve') / 100, 2, '.', ''));

    $crumbs = variantJsonLdOfType($html, 'BreadcrumbList');
    expect(array_column($crumbs['itemListElement'], 'name'))->toBe(['Home', 'Mechanics Liens', 'Notice of Intent to Lien', 'Colorado'])
        ->and($crumbs['itemListElement'][2]['item'])->toBe(route('liens.notice-of-intent-to-lien'));
});

it('renders Texas as not required, with the lien deadline it sits in front of and the free form', function () {
    $html = $this->get('/liens/notice-of-intent-to-lien/texas')->assertOk()->getContent();
    $text = variantVisibleText($html);

    expect($text)->toContain('Not required. Texas does not require a notice of intent before a lien.')
        ->toContain('(Tex. Prop. Code §§ 53.054, 53.056)')
        ->toContain('A notice of intent is a final written demand.')
        ->toContain('The Texas lien deadline is by the 15th day of the 3rd month')
        ->toContain('Send it by certified mail, return receipt requested')
        ->toContain('$'.(config('lien.pricing.noi.self_serve') / 100))
        ->and($html)->toContain(route('liens.notice-of-intent-to-lien.state.blank', ['state' => 'texas']))
        ->toContain('Notice of Intent to File a Lien Affidavit')
        ->toContain('href="'.route('liens.state', ['state' => 'texas']).'"')
        ->toContain('href="'.route('liens.lien-waivers.state', ['state' => 'tx']).'"')
        ->toContain('href="'.route('liens.lien-release.state', ['state' => 'texas']).'"')
        ->toContain('href="'.route('liens').'"')
        // Bordering states with a page: Oklahoma has none, Louisiana and Arkansas do.
        ->toContain('href="'.route('liens.notice-of-intent-to-lien.state', ['state' => 'louisiana']).'"')
        ->toContain('href="'.route('liens.notice-of-intent-to-lien.state', ['state' => 'arkansas']).'"');

    expect(variantJsonLdOfType($html, 'FAQPage')['mainEntity'])->toHaveCount(5)
        ->and(variantJsonLdOfType($html, 'Service')['offers']['@type'])->toBe('Offer');
});

it('states the forward-counting Kentucky rule, not the engine row', function () {
    $text = variantVisibleText($this->get('/liens/notice-of-intent-to-lien/kentucky')->assertOk()->getContent());

    expect($text)->toContain('It counts forward from your last day of work or materials, not back from the lien.')
        ->toContain('within 120 days after last furnishing on claims over $1,000')
        ->toContain('(KRS 376.010(4), (5))')
        ->not->toContain('120 days before filing')
        // No Kentucky document file, so no blank form.
        ->toContain('Not yet for Kentucky');
});

it('states the statute rule for Maryland, New Jersey and Tennessee', function () {
    expect(variantVisibleText($this->get('/liens/notice-of-intent-to-lien/maryland')->getContent()))
        ->toContain('within 120 days after doing the work or furnishing the materials')
        ->not->toContain('120 days before filing');

    expect(variantVisibleText($this->get('/liens/notice-of-intent-to-lien/new-jersey')->getContent()))
        ->toContain('Notice of Unpaid Balance and Right to File Lien with the county clerk within 60 days after last furnishing')
        ->not->toContain('60 days before filing');

    expect(variantVisibleText($this->get('/liens/notice-of-intent-to-lien/tennessee')->getContent()))
        ->toContain('within 90 days after the last day of each month in which work or materials went unpaid')
        ->not->toContain('90 days before filing');
});

it('renders the Texas lien release page', function () {
    $html = $this->get('/liens/lien-release/texas')->assertOk()->getContent();
    $text = variantVisibleText($html);

    expect($text)->toContain('Texas Mechanics Lien Release')
        ->toContain('Within 10 days after a written request, once the debt is paid')
        ->toContain('Source: Tex. Prop. Code § 53.152.')
        ->toContain('Record it with the county clerk where the property sits.')
        ->toContain('Not confirmed in our research. Confirm with counsel.')
        ->and($html)->toContain('<link rel="canonical" href="'.url('/liens/lien-release/texas').'"')
        ->toContain(route('liens.lien-release.state.blank', ['state' => 'texas']))
        ->toContain('href="'.route('liens.notice-of-intent-to-lien.state', ['state' => 'texas']).'"');

    expect(variantJsonLdOfType($html, 'FAQPage')['mainEntity'])->toHaveCount(5)
        ->and(variantJsonLdOfType($html, 'Service')['offers']['price'])->toBe(number_format(config('lien.pricing.lien_release.self_serve') / 100, 2, '.', ''))
        ->and(array_column(variantJsonLdOfType($html, 'BreadcrumbList')['itemListElement'], 'name'))->toBe(['Home', 'Mechanics Liens', 'Lien Release', 'Texas']);
});

it('serves all forty pages within the title and description budgets', function () {
    foreach ([NoticeOfIntentStatePage::class, LienReleaseStatePage::class] as $model) {
        foreach ($model::availableStates() as $code => $name) {
            $page = $model::forCode($code);
            $path = parse_url($page->url(), PHP_URL_PATH);

            $html = $this->get($path)->assertOk()->getContent();

            expect(mb_strlen($page->title()))->toBeLessThanOrEqual(60, $page->title())
                ->and(mb_strlen($page->metaDescription()))->toBeGreaterThanOrEqual(70, $page->metaDescription())
                ->toBeLessThanOrEqual(165, $page->metaDescription())
                ->and($page->faq())->toHaveCount(5)
                ->and($html)->toContain(e($page->title()))
                ->toContain('Confirm with counsel', $path);
        }
    }
});

it('redirects two-letter codes to the slug and 404s unknown states', function () {
    $this->get('/liens/notice-of-intent-to-lien/tx')->assertStatus(301)
        ->assertRedirect(route('liens.notice-of-intent-to-lien.state', ['state' => 'texas']));
    $this->get('/liens/notice-of-intent-to-lien/CO')->assertStatus(301)
        ->assertRedirect(route('liens.notice-of-intent-to-lien.state', ['state' => 'colorado']));
    $this->get('/liens/lien-release/tx')->assertStatus(301)
        ->assertRedirect(route('liens.lien-release.state', ['state' => 'texas']));
    $this->get('/liens/lien-release/TX/blank.pdf')->assertStatus(301)
        ->assertRedirect(route('liens.lien-release.state.blank', ['state' => 'texas']));

    $this->get('/liens/notice-of-intent-to-lien/atlantis')->assertNotFound();
    $this->get('/liens/lien-release/atlantis')->assertNotFound();
    // A real state outside the twenty has no page.
    $this->get('/liens/notice-of-intent-to-lien/vermont')->assertNotFound();
    $this->get('/liens/lien-release/vt')->assertNotFound();
});

it('serves the three blank Texas PDFs as cached attachments', function (string $url, string $filename) {
    $response = $this->get($url)->assertOk();

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toBe('attachment; filename="'.$filename.'"')
        ->and(substr($response->getContent(), 0, 4))->toBe('%PDF');

    $files = Storage::disk('local')->allFiles(BlankLienDocument::CACHE_DIRECTORY);
    expect($files)->toHaveCount(1);

    // The second request is served from the cached file.
    Storage::disk('local')->put($files[0], '%PDF-cached');
    expect($this->get($url)->getContent())->toBe('%PDF-cached');
})->with([
    'notice of intent' => ['/liens/notice-of-intent-to-lien/texas/blank.pdf', 'texas-notice-of-intent-to-file-a-lien-affidavit-blank.pdf'],
    'lien release' => ['/liens/lien-release/texas/blank.pdf', 'texas-release-of-lien-blank.pdf'],
    'demand letter' => ['/liens/payment-demand-letter/blank.pdf', BlankDemandLetter::FILENAME],
]);

it('caches each kind under its own key', function () {
    $blanks = app(BlankLienDocument::class);

    expect($blanks->cachePath($blanks->form('TX', 'noi')))->toStartWith(BlankLienDocument::CACHE_DIRECTORY.'/v'.BlankLienDocument::CACHE_VERSION.'/noi/tx-t')
        ->and($blanks->cachePath($blanks->form('TX', 'lien_release')))->toStartWith(BlankLienDocument::CACHE_DIRECTORY.'/v'.BlankLienDocument::CACHE_VERSION.'/lien_release/tx-t')
        ->and(app(BlankDemandLetter::class)->cachePath())->toStartWith(BlankLienDocument::CACHE_DIRECTORY.'/v'.BlankLienDocument::CACHE_VERSION.'/demand_letter/');
});

it('prints the blank notice and release with no names, amounts or preparer', function (string $kind, string $heading) {
    $blanks = app(BlankLienDocument::class);
    $form = $blanks->form('TX', $kind);
    $html = view(app(\App\Domains\Lien\Documents\LienDocumentGenerator::class)->layout($form), ['doc' => $blanks->payload($form)])->render();

    expect($html)->toContain($heading)
        ->not->toContain('eRegister</div>')
        ->not->toContain(now()->eastern()->format('F j, Y'));
})->with([
    'notice of intent' => ['noi', 'NOTICE OF INTENT TO FILE A LIEN AFFIDAVIT'],
    'lien release' => ['lien_release', 'RELEASE OF LIEN'],
]);

it('prints the blank demand letter with ruled lines and no date', function () {
    $html = view(BlankDemandLetter::VIEW, ['letter' => app(BlankDemandLetter::class)->data()])->render();

    expect($html)->toContain('This letter serves as a formal demand for payment')
        ->toContain('$______________')
        ->not->toContain('[amount due]')
        ->not->toContain(now()->eastern()->format('F j, Y'));
});

it('404s the blank PDF for a state without a document of that kind', function () {
    // Kentucky has a page but no lien_documents file; Vermont has no page.
    $this->get('/liens/notice-of-intent-to-lien/kentucky/blank.pdf')->assertNotFound();
    $this->get('/liens/lien-release/kentucky/blank.pdf')->assertNotFound();
    $this->get('/liens/lien-release/maryland/blank.pdf')->assertNotFound();
    $this->get('/liens/notice-of-intent-to-lien/vermont/blank.pdf')->assertNotFound();

    expect(variantVisibleText($this->get('/liens/lien-release/kentucky')->getContent()))
        ->not->toContain('Download the free blank form');
});

it('renders a blank notice and release for every page state with a document file', function () {
    $blanks = app(BlankLienDocument::class);

    foreach (['noi', 'lien_release'] as $kind) {
        foreach (LienVariantStatePage::STATES as $code) {
            $expected = is_file(database_path('data/lien_documents/'.strtolower($code).'.php'));

            expect($blanks->available($code, $kind))->toBe($expected, "{$kind} {$code}");

            if ($expected) {
                expect(substr((string) $blanks->pdf($code, $kind), 0, 4))->toBe('%PDF', "{$kind} {$code}");
            }
        }
    }
});

it('lists the forty pages in the sitemap and none of the PDFs', function () {
    $locs = array_column(SitemapController::urls(), 'loc');

    foreach (LienVariantStatePage::STATES as $code) {
        $slug = States::slug(States::name($code));
        expect($locs)->toContain(url('/liens/notice-of-intent-to-lien/'.$slug))
            ->toContain(url('/liens/lien-release/'.$slug));
    }

    $variants = array_filter($locs, fn (string $loc) => preg_match('#/liens/(notice-of-intent-to-lien|lien-release)/[a-z-]+$#', $loc));
    expect($variants)->toHaveCount(40)
        ->and(array_filter($locs, fn (string $loc) => str_ends_with($loc, '.pdf')))->toBe([]);

    $entries = collect(SitemapController::urls())->keyBy('loc');
    expect($entries[url('/liens/lien-release/texas')]['priority'])->toBe('0.6');
});

it('links the state grids from the service pages and the variants from the lien state pages', function () {
    $notice = $this->get('/liens/notice-of-intent-to-lien')->assertOk()->getContent();
    $release = $this->get('/liens/lien-release')->assertOk()->getContent();

    foreach (LienVariantStatePage::STATES as $code) {
        $slug = States::slug(States::name($code));
        expect($notice)->toContain('href="'.route('liens.notice-of-intent-to-lien.state', ['state' => $slug]).'"')
            ->and($release)->toContain('href="'.route('liens.lien-release.state', ['state' => $slug]).'"');
    }

    $this->get('/liens/colorado')->assertOk()
        ->assertSee('href="'.route('liens.notice-of-intent-to-lien.state', ['state' => 'colorado']).'"', false)
        ->assertSee('href="'.route('liens.lien-release.state', ['state' => 'colorado']).'"', false);

    $this->get('/liens/vermont')->assertOk()
        ->assertDontSee('/liens/notice-of-intent-to-lien/vermont', false);
});

it('links the free demand letter template from the demand letter page', function () {
    $this->get('/liens/payment-demand-letter')->assertOk()
        ->assertSee('href="'.route('liens.payment-demand-letter.blank').'"', false)
        ->assertSee('Download a free payment demand letter template', false);
});
