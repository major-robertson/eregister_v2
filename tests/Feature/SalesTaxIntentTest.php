<?php

it('swaps the hero keyword for a whitelisted intent', function (string $intent, string $expected) {
    $this->get("/sales-tax-registration?intent={$intent}")
        ->assertOk()
        ->assertSee("<span data-hero-keyword style=\"color: var(--color-accent)\">{$expected}</span>", false);
})->with([
    'registration' => ['sales-tax-registration', 'Sales Tax'],
    'permit' => ['sales-tax-permit', 'Sales Tax Permit'],
    'id' => ['sales-tax-id', 'Sales Tax ID'],
]);

it('defaults to the registration keyword when no intent is provided', function () {
    $this->get('/sales-tax-registration')
        ->assertOk()
        ->assertSee('<span data-hero-keyword style="color: var(--color-accent)">Sales Tax</span>', false);
});

it('defaults to the registration keyword for an unknown intent', function () {
    $this->get('/sales-tax-registration?intent=bogus')
        ->assertOk()
        ->assertSee('<span data-hero-keyword style="color: var(--color-accent)">Sales Tax</span>', false);
});
