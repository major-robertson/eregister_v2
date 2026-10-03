<?php

use App\Http\Controllers\SitemapController;
use App\Support\Seo\States;

/*
 * Every URL the sitemap hands to Google must be a clean, indexable page that
 * names itself as canonical. Each page is rendered once and every check runs
 * on that HTML; failures are collected per path so one run lists them all.
 */

// The only pages whose titles end with " | eRegister" (decision D3).
const BRANDED_TITLE_PATHS = ['/', '/privacy-policy', '/terms-of-service', '/refund-policy', '/contact', '/about'];

// The street address is never published on the website (owner, 2026-10-03):
// not in copy, footers or JSON-LD. Word boundaries keep asset hashes out.
const STREET_ADDRESS_PATTERN = '/Brownsboro|\b40207\b|PostalAddress/i';

// Working notes from the lien seed data that must never reach a page.
const AUTHORING_PHRASES = '/this sheet|modeled here|as proxy|placeholder|See the notes below|See statute|plan on paper recording|snake_case/i';
const RAW_FIELD_NAMES = ['lien_anchor_logic', 'enforcement_trigger', 'first_furnish_date'];

it('serves every sitemap url as a clean, self-canonical, indexable page', function () {
    $lienStatePaths = array_map(fn (string $name) => '/liens/'.States::slug($name), States::names());
    $failures = [];
    $seenLienStates = [];

    foreach (SitemapController::urls() as $entry) {
        $loc = $entry['loc'];
        $path = parse_url($loc, PHP_URL_PATH) ?: '/';
        $fail = function (string $message) use (&$failures, $path) {
            $failures[$path][] = $message;
        };

        $response = $this->get($path);
        if ($response->status() !== 200) {
            $fail("status {$response->status()}");

            continue;
        }
        $html = $response->getContent();

        preg_match_all('/<link rel="canonical" href="([^"]*)"/', $html, $canonicals);
        if (count($canonicals[1]) !== 1) {
            $fail(count($canonicals[1]).' canonical tags');
        } elseif ($canonicals[1][0] !== $loc) {
            $fail("canonical {$canonicals[1][0]} is not {$loc}");
        }

        preg_match_all('/<meta name="robots" content="([^"]*)"/', $html, $robots);
        if (! $robots[1]) {
            $fail('no robots meta');
        }
        foreach ($robots[1] as $content) {
            if (! str_contains($content, 'index') || str_contains($content, 'noindex')) {
                $fail("robots meta \"{$content}\"");
            }
        }

        foreach (['<title' => '/<title[\s>]/', '<meta name="description"' => '/<meta name="description"/', '<h1' => '/<h1[\s>]/'] as $tag => $pattern) {
            $count = preg_match_all($pattern, $html);
            if ($count !== 1) {
                $fail("{$count} {$tag} tags");
            }
        }

        preg_match('/<title>(.*?)<\/title>/s', $html, $title);
        $title = html_entity_decode(trim($title[1] ?? ''), ENT_QUOTES);
        if (mb_strlen($title) > 70) {
            $fail('title is '.mb_strlen($title)." characters: {$title}");
        }
        // Decision D3: only home, the legal pages, /contact and /about carry the
        // brand; product, state and government titles spend it on the query.
        $branded = str_ends_with($title, ' | eRegister');
        if (in_array($path, BRANDED_TITLE_PATHS, true) && ! $branded) {
            $fail("title does not end with \" | eRegister\": {$title}");
        } elseif (! in_array($path, BRANDED_TITLE_PATHS, true) && $branded) {
            $fail("title carries the brand suffix: {$title}");
        }

        preg_match('/<meta name="description"\s+content="([^"]*)"/', $html, $description);
        $description = html_entity_decode($description[1] ?? '', ENT_QUOTES);
        $length = mb_strlen($description);
        if ($length < 70 || $length > 165) {
            $fail("description is {$length} characters: {$description}");
        }

        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $blocks);
        foreach ($blocks[1] as $json) {
            $data = json_decode($json, true);
            if (! is_array($data) || ! isset($data['@type'])) {
                $fail('JSON-LD block without @type or not valid JSON: '.mb_substr(trim($json), 0, 80));
            }
        }

        if (preg_match(STREET_ADDRESS_PATTERN, $html, $match)) {
            $fail("publishes the street address (\"{$match[0]}\")");
        }

        foreach (['href="#"', '&amp;amp;', '@eregister.com', 'mailto:'] as $needle) {
            if (str_contains($html, $needle)) {
                $fail("contains {$needle}");
            }
        }

        // Visible text only: form fields legitimately carry placeholder="...".
        $text = strip_tags(preg_replace('/<(script|style)\b.*?<\/\1>/si', ' ', $html));
        if (preg_match(AUTHORING_PHRASES, $text, $match)) {
            $fail("authoring phrase \"{$match[0]}\"");
        }
        foreach (RAW_FIELD_NAMES as $field) {
            if (str_contains($html, $field)) {
                $fail("raw field name {$field}");
            }
        }

        if (in_array($path, $lienStatePaths, true)) {
            $seenLienStates[] = $path;
            if (stripos($html, 'confirm with counsel') === false) {
                $fail('no "confirm with counsel" disclaimer');
            }
            // Researched notes print in their own box on every state page.
            if (! preg_match('/Practitioner notes for [^<]+<\/h3>\s*<p[^>]*>\s*\S/', $html)) {
                $fail('no "Practitioner notes" box with text');
            }
            // Seed-data tokens such as last_furnish_date or lien_anchor_logic.
            if (preg_match('/\w+_(?:date|logic)\b/', $text, $match)) {
                $fail("raw field token \"{$match[0]}\"");
            }
        }
    }

    foreach (array_diff($lienStatePaths, $seenLienStates) as $missing) {
        $failures[$missing][] = 'lien state page missing from the sitemap';
    }

    $report = collect($failures)
        ->map(fn (array $messages, string $path) => $path."\n  - ".implode("\n  - ", $messages))
        ->implode("\n");

    expect($failures)->toBeEmpty("Sitemap pages that break the contract:\n".$report);
});
