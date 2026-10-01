<?php

use App\Support\Seo\Urls;

uses(Tests\TestCase::class);

it('builds absolute urls from the configured app url, not the request', function () {
    config(['app.url' => 'https://eregister.com/']);

    expect(Urls::absolute('/'))->toBe('https://eregister.com/')
        ->and(Urls::absolute(''))->toBe('https://eregister.com/')
        ->and(Urls::absolute('/llc'))->toBe('https://eregister.com/llc')
        ->and(Urls::absolute('liens/texas'))->toBe('https://eregister.com/liens/texas');
});
