<?php

use App\Http\Controllers\SitemapController;

it('keeps the sign-in screens out of search', function (string $path) {
    $this->get($path)
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex"', escape: false);
})->with([
    'login' => '/login',
    'register' => '/register',
    'forgot password' => '/forgot-password',
]);

// /styleguide sits behind auth, so it is not requested here; its portal
// layout carries the same tag as the sign-in screens.
it('keeps demos and old copies out of search', function (string $path) {
    $html = $this->get($path)->assertOk()->getContent();

    expect($html)->toMatch('/<meta name="robots" content="noindex[^"]*"/');
})->with([
    'landing2' => '/landing2',
    'florida eog demo 1' => '/government/florida-eog-demo-1',
    'florida eog demo 2' => '/government/florida-eog-demo-2',
    'clay demo' => '/clay-demo',
    'mdcps demo' => '/mdcps-demo',
    'pcc demo' => '/pcc-demo',
]);

it('leaves every noindexed page out of the sitemap', function () {
    $paths = array_map(fn (string $loc) => parse_url($loc, PHP_URL_PATH) ?: '/', array_column(SitemapController::urls(), 'loc'));

    foreach (['/login', '/register', '/forgot-password', '/landing2', '/styleguide', '/government/florida-eog-demo-1', '/government/florida-eog-demo-2'] as $path) {
        expect($paths)->not->toContain($path);
    }

    foreach (['/clay-demo', '/mdcps-demo', '/pcc-demo'] as $prefix) {
        foreach ($paths as $path) {
            expect(str_starts_with($path, $prefix))->toBeFalse("{$path} is a demo page");
        }
    }
});
