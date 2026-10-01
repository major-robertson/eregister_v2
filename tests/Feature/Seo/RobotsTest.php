<?php

// robots.txt is a static file that nginx serves before Laravel boots, so the
// test kernel cannot GET it; the file itself is the contract.
beforeEach(function () {
    $this->robots = file_get_contents(public_path('robots.txt'));
});

it('points crawlers at the sitemap', function () {
    expect($this->robots)->toContain('Sitemap: https://eregister.com/sitemap.xml');
});

it('blocks the private areas', function (string $path) {
    expect($this->robots)->toMatch('/^Disallow: '.preg_quote($path, '/').'$/m');
})->with(['/admin', '/portal', '/go/', '/r/', '/api/', '/storage/']);

// A robots.txt block would stop Google from ever reading their noindex tag.
it('does not block the noindexed demo pages', function (string $path) {
    expect($this->robots)->not->toMatch('/^Disallow: '.preg_quote($path, '/').'/m');
})->with(['/landing2', '/styleguide', '/clay-demo', '/mdcps-demo', '/pcc-demo', '/government/florida-eog-demo']);
