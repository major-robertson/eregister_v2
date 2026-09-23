<?php

use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Domains\ResaleCert\Seo\ResaleStatePage;
use App\Http\Controllers\SitemapController;
use App\Support\Seo\States;

it('returns a valid xml sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    foreach (SitemapController::urls() as $entry) {
        $response->assertSee("<loc>{$entry['loc']}</loc>", escape: false);
    }
});

it('includes a lastmod date on every entry', function () {
    // The data sources the state-page lastmods are derived from must exist,
    // otherwise the dates silently fall back to the template's mtime.
    expect(database_path('data/waiver_states'))->toBeDirectory()
        ->and(database_path('seeders/data/lien_state_rules.json'))->toBeFile()
        ->and(database_path('seeders/data/lien_deadline_rules.json'))->toBeFile();

    foreach (SitemapController::urls() as $entry) {
        expect($entry['lastmod'])->toMatch('/^\d{4}-\d{2}-\d{2}$/');
    }

    $this->get('/sitemap.xml')->assertSee('<lastmod>', escape: false);
});

it('includes the lien pricing page and no noindexed pages', function () {
    $locs = array_column(SitemapController::urls(), 'loc');

    expect($locs)->toContain(url('/liens/pricing'))
        ->not->toContain(url('/landing2'))
        ->not->toContain(url('/government/florida-eog-demo-1'));
});

it('includes the lien waiver seo pages', function () {
    $locs = array_column(SitemapController::urls(), 'loc');

    expect($locs)->toContain(url('/liens/lien-waivers'))
        ->toContain(url('/liens/lien-waivers/pricing'));

    foreach (array_keys(WaiverStateRegistry::STATE_NAMES) as $code) {
        expect($locs)->toContain(url('/liens/lien-waivers/'.strtolower($code)));
    }
});

it('includes the mechanics lien and resale certificate state pages', function () {
    $locs = array_column(SitemapController::urls(), 'loc');

    foreach (States::names() as $name) {
        expect($locs)->toContain(url('/liens/'.States::slug($name)));
    }

    $resaleStates = ResaleStatePage::availableStates();
    expect($resaleStates)->not->toBeEmpty()->not->toHaveKey('MTC');

    foreach ($resaleStates as $name) {
        expect($locs)->toContain(url('/resale-certificates/'.States::slug($name)));
    }
});
