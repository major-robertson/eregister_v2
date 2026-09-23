<?php

it('permanently redirects the previous product\'s paths to the pages that replaced them', function (string $from, string $to) {
    $this->get($from)
        ->assertStatus(301)
        ->assertRedirect($to);
})->with([
    ['/sales-tax', '/sales-tax-registration'],
    ['/sales-tax/pricing', '/sales-tax-registration'],
    ['/formation/pricing', '/llc'],
    ['/pricing', '/sales-tax-registration'],
]);

it('keeps the query string off the redirect target', function () {
    // Old ads append tracking parameters; the redirect target is the clean page.
    $this->get('/sales-tax?gclid=abc')->assertRedirect('/sales-tax-registration');
});
