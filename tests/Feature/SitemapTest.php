<?php

use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Http\Controllers\SitemapController;

it('returns a valid xml sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    foreach (SitemapController::urls() as $entry) {
        $response->assertSee("<loc>{$entry['loc']}</loc>", escape: false);
    }
});

it('includes the lien waiver seo pages', function () {
    $locs = array_column(SitemapController::urls(), 'loc');

    expect($locs)->toContain(url('/liens/lien-waivers'))
        ->toContain(url('/liens/lien-waivers/pricing'));

    foreach (array_keys(WaiverStateRegistry::STATE_NAMES) as $code) {
        expect($locs)->toContain(url('/liens/lien-waivers/'.strtolower($code)));
    }
});
