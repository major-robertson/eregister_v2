<?php

it('swaps the hero keyword for a whitelisted intent', function (string $intent, string $expected) {
    $this->get("/sales-tax-registration?intent={$intent}")
        ->assertOk()
        ->assertSee("Sales & Use Tax <span class=\"text-blue-600\">{$expected}</span>", false);
})->with([
    'registration' => ['sales-tax-registration', 'Registration'],
    'permit' => ['sales-tax-permit', 'Permit'],
    'id' => ['sales-tax-id', 'ID'],
]);

it('defaults to Registration when no intent is provided', function () {
    $this->get('/sales-tax-registration')
        ->assertOk()
        ->assertSee('Sales & Use Tax <span class="text-blue-600">Registration</span>', false);
});

it('defaults to Registration for an unknown intent', function () {
    $this->get('/sales-tax-registration?intent=bogus')
        ->assertOk()
        ->assertSee('Sales & Use Tax <span class="text-blue-600">Registration</span>', false);
});
