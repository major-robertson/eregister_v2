<?php

use Illuminate\Support\Facades\Blade;

/*
 * The logo files are real outlined-text vectors, not PNGs wrapped in an
 * <svg> (EREG-12), and the <img> tags carry intrinsic dimensions so the
 * header reserves its space before the file loads.
 */

dataset('vector logos', [
    'eregister-logo-dark-svg.svg',
    'eregister-logo-light-svg.svg',
    'eregister-icon-dark-svg.svg',
    'eregister-icon-light-svg.svg',
]);

it('ships the logo as a small outlined vector', function (string $file) {
    $path = public_path('img/logo/'.$file);

    expect(file_exists($path))->toBeTrue("{$file} is missing")
        ->and(filesize($path))->toBeLessThan(8 * 1024);

    $svg = file_get_contents($path);

    expect($svg)->toStartWith('<svg')
        ->toContain('<path')
        ->not->toContain('<image')
        ->not->toContain('data:')
        ->not->toContain('<text');
})->with('vector logos');

it('gives the landing header logo its intrinsic dimensions', function () {
    $this->get('/')->assertOk()
        ->assertSee('<img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" width="1538" height="520" class="h-9 w-auto" />', false);
});

it('gives the government header logo its intrinsic dimensions', function () {
    $this->get('/government')->assertOk()
        ->assertSee('<img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" width="1538" height="520" class="h-9 w-auto" />', false);
});

it('gives the login page icon its intrinsic dimensions', function () {
    $this->get('/login')->assertOk()
        ->assertSee('<img src="/img/logo/eregister-icon-dark-svg.svg" alt="eRegister" width="512" height="512" class="size-14" />', false);
});

it('gives the split auth layout light logo its intrinsic dimensions', function () {
    $html = Blade::render('<x-layouts::auth.split>Sign in</x-layouts::auth.split>');

    expect($html)->toContain('<img src="/img/logo/eregister-logo-light-svg.svg" alt="eRegister" width="1538" height="520" class="h-9 w-auto" />');
});
