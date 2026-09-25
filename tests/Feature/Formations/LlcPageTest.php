<?php

use App\Models\Price;
use Database\Seeders\FormationFeeSeeder;

beforeEach(function () {
    (new FormationFeeSeeder)->run();
});

it('shows the membership price the LLC checkout charges', function () {
    $this->get('/llc')
        ->assertOk()
        ->assertSeeInOrder(['Complete LLC Package', '$299', 'Plus state filing fees'])
        ->assertDontSee('$297');
});

it('reads the membership price from the prices table in both places', function () {
    Price::resolve('formation', 'llc', 'membership', 'subscription')->update(['amount_cents' => 34900]);

    $this->get('/llc')
        ->assertOk()
        ->assertSeeInOrder(['Complete LLC Package', '$349', 'Plus state filing fees', 'Total (first year)', '$349']);
});
