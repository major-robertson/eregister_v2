<?php

it('renders the demo pages without auth', function (string $uri, string $expected) {
    $this->get($uri)
        ->assertOk()
        ->assertSee($expected, escape: false);
})->with([
    'home' => ['/pcc-demo', 'Your future is closer than you think.'],
    'program finder' => ['/pcc-demo/programs', 'Find your program.'],
    'finder with area filter' => ['/pcc-demo/programs?area=Health+Sciences', 'Health Sciences'],
    'program page' => ['/pcc-demo/programs/welding-technology', 'Welding Technology'],
    'short-term program page' => ['/pcc-demo/programs/truck-driver-training', 'Under $2,000'],
    'admissions' => ['/pcc-demo/admissions', 'pick the one that fits you'],
    'admissions pathway preselect' => ['/pcc-demo/admissions?type=adult', 'Adult Learner'],
    'paying for college' => ['/pcc-demo/paying-for-college', 'College you can actually afford.'],
    'student life' => ['/pcc-demo/student-life', 'More than classes.'],
    'workforce' => ['/pcc-demo/workforce', 'talent partner'],
    'about' => ['/pcc-demo/about', 'since 1961'],
]);

it('keeps the demo noindexed', function () {
    $this->get('/pcc-demo')
        ->assertOk()
        ->assertSee('noindex', escape: false);
});

it('404s unknown program slugs', function () {
    $this->get('/pcc-demo/programs/underwater-basket-weaving')->assertNotFound();
});

it('merges program defaults into every record', function () {
    // Welding overrides "next" but inherits cost and deadlines from defaults.
    $this->get('/pcc-demo/programs/welding-technology')
        ->assertSee('$76/credit')
        ->assertSee('Tour the welding shop and meet your advisor.', escape: false)
        ->assertSee('FAFSA priority date');
});
