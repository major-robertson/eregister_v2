<?php

use App\Domains\ResaleCert\Seo\ResaleStateContent;
use App\Domains\ResaleCert\Seo\ResaleStatePage;
use App\Support\Seo\States;

if (! function_exists('resaleContentBodyText')) {
    /** The visible text of a page's <main> element: scripts, styles and SVGs dropped, tags stripped. */
    function resaleContentBodyText(string $html): string
    {
        $main = preg_match('/<main\b[^>]*>(.*)<\/main>/s', $html, $m) ? $m[1] : $html;
        $main = preg_replace('/<(script|style|svg)\b[^>]*>.*?<\/\1>/s', ' ', $main);

        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($main), ENT_QUOTES | ENT_HTML5)));
    }
}

if (! function_exists('resaleContentShingles')) {
    /** @return array<string, true> 6-word shingles of the text, as set keys. */
    function resaleContentShingles(string $text): array
    {
        $words = preg_split('/\s+/u', mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text)), -1, PREG_SPLIT_NO_EMPTY);
        $set = [];
        for ($i = 0, $n = count($words) - 5; $i < $n; $i++) {
            $set[implode(' ', array_slice($words, $i, 6))] = true;
        }

        return $set;
    }
}

if (! function_exists('resaleContentRegistrableDomain')) {
    /** "comptroller.texas.gov" => "texas.gov". */
    function resaleContentRegistrableDomain(string $url): string
    {
        return implode('.', array_slice(explode('.', (string) parse_url($url, PHP_URL_HOST)), -2));
    }
}

if (! function_exists('resaleContentRenderAll')) {
    /** @return array<string, string> code => rendered HTML for every resale state page */
    function resaleContentRenderAll($test): array
    {
        $pages = [];
        foreach (ResaleStatePage::availableStates() as $code => $name) {
            $pages[$code] = $test->get('/resale-certificates/'.States::slug($name))->assertOk()->getContent();
        }

        return $pages;
    }
}

describe('resale state content files', function () {
    it('has a content file for every resale state page', function () {
        $codes = array_keys(ResaleStatePage::availableStates());
        expect($codes)->not->toBeEmpty();

        foreach ($codes as $code) {
            expect(ResaleStateContent::exists($code))->toBeTrue("{$code} has no database/data/resale_states file");
        }

        expect(ResaleStateContent::for('ZZ'))->toBeNull();
    });

    it('gives every file an agency, a form, plain-English notes and sourced facts', function () {
        $files = glob(ResaleStateContent::directory().'/*.php');
        expect($files)->not->toBeEmpty();

        foreach ($files as $file) {
            $code = strtoupper(basename($file, '.php'));
            $data = require $file;

            expect($data['state'])->toBe($code)
                ->and($data['agency']['name'] ?? null)->toBeString()->not->toBeEmpty()
                ->and($data['agency']['url'] ?? '')->toStartWith('https://')
                ->and($data['form'] ?? null)->toBeArray()
                ->and($data['issuer_model'])->toBeIn(['state_issued', 'purchaser_completed']);

            $words = str_word_count($data['state_notes'] ?? '');
            expect($words)->toBeGreaterThanOrEqual(120, "{$code}: state_notes has {$words} words")
                ->toBeLessThanOrEqual(300, "{$code}: state_notes has {$words} words");

            expect(count($data['facts'] ?? []))->toBeGreaterThanOrEqual(3, "{$code}: fewer than 3 facts");
            foreach ($data['facts'] as $fact) {
                expect($fact['text'])->not->toBeEmpty()
                    ->and($fact['source_url'])->toStartWith('https://');
            }

            foreach ($data['accepts'] as $key => $rule) {
                expect($rule['value'])->toBeIn([true, false, null], "{$code}: accepts.{$key}");
            }
        }
    });
});

describe('rendered resale state pages', function () {
    it('names the real agency and form and links out to the agency', function () {
        foreach (resaleContentRenderAll($this) as $code => $html) {
            $content = ResaleStateContent::for($code);
            $agency = $content['agency']['name'];
            $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5);

            // The old default ("{State} Department of Revenue") must not survive anywhere it is wrong.
            if (! str_contains($agency, 'Department of Revenue')) {
                expect(str_contains($text, 'Department of Revenue'))->toBeFalse("{$code}: says Department of Revenue");
            }

            expect(str_contains($text, $agency))->toBeTrue("{$code}: agency name missing");

            if ($number = $content['form']['number'] ?? null) {
                expect(str_contains($text, $number))->toBeTrue("{$code}: form number missing");
            }

            preg_match_all('/href="(https:\/\/[^"]+)"/', $html, $matches);
            $domain = resaleContentRegistrableDomain($content['agency']['url']);
            $agencyLinks = collect($matches[1])
                ->map(fn (string $href) => html_entity_decode($href, ENT_QUOTES | ENT_HTML5))
                ->filter(function (string $href) use ($domain) {
                    $host = (string) parse_url($href, PHP_URL_HOST);

                    return $host === $domain || str_ends_with($host, '.'.$domain);
                })
                ->unique();

            expect($agencyLinks->count())->toBeGreaterThanOrEqual(2, "{$code}: fewer than two links to {$domain}");
        }
    });

    it('reads differently from every other state page', function () {
        $sets = [];
        foreach (resaleContentRenderAll($this) as $code => $html) {
            $sets[$code] = resaleContentShingles(resaleContentBodyText($html));
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

    it('renders the Texas form, agency, sources and researched rules', function () {
        $html = $this->get('/resale-certificates/texas')->assertOk()
            ->assertSee('Form 01-339', escape: false)
            ->assertSee('Texas Comptroller of Public Accounts', escape: false)
            ->assertSee('Texas rules in plain English', escape: false)
            ->assertSee('More Texas facts', escape: false)
            ->assertSee('What happens if I misuse a Texas resale certificate?', escape: false)
            ->assertSee('When can a Texas seller rely on a resale certificate?', escape: false)
            ->assertSee('How do I verify a Texas resale certificate?', escape: false)
            ->assertSee('href="https://comptroller.texas.gov/forms/01-339.pdf" rel="noopener" target="_blank"', escape: false)
            ->getContent();

        expect($html)->not->toContain('nofollow');

        $page = ResaleStatePage::forCode('TX');
        expect($page->acceptsMtc())->toBeTrue()
            ->and($page->metaDescription())->toContain('Form 01-339')
            ->and($page->formLabel())->toStartWith('Form 01-339 (');
    });

    it('never presents Alaska as having a state sales tax permit', function () {
        $text = resaleContentBodyText($this->get('/resale-certificates/alaska')->assertOk()->getContent());

        expect($text)->toContain('Alaska Remote Seller Sales Tax Commission')
            ->not->toMatch('/Alaska (?:state )?sales tax permit/i')
            ->not->toContain('Register for Alaska sales tax');
    });
});
