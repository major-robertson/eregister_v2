<?php

use App\Http\Controllers\SitemapController;

it('returns a valid xml sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml');

    foreach (SitemapController::urls() as $entry) {
        $response->assertSee("<loc>{$entry['loc']}</loc>", escape: false);
    }
});
