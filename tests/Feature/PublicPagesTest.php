<?php

it('can render all public pages', function (string $uri) {
    $this->get($uri)->assertOk();
})->with([
    'home' => '/',
    'llc' => '/llc',
    'liens' => '/liens',
    'contact' => '/contact',
    'privacy-policy' => '/privacy-policy',
    'terms-of-service' => '/terms-of-service',
    'refund-policy' => '/refund-policy',
    // Form a Business - Register
    'corporation' => '/corporation',
    'dba' => '/dba',
    'nonprofit' => '/nonprofit',
    'sole-proprietorship' => '/sole-proprietorship',
    // Form a Business - Run
    'registered-agent' => '/registered-agent',
    'annual-reports' => '/annual-reports',
    'ein-tax-id' => '/ein-tax-id',
    'operating-agreement' => '/operating-agreement',
    // Compliance & Tax
    'sales-tax-registration' => '/sales-tax-registration',
    'resale-certificates' => '/resale-certificates',
    // Payment Protection (lien sub-pages)
    'preliminary-notice' => '/liens/preliminary-notice',
    'notice-of-intent-to-lien' => '/liens/notice-of-intent-to-lien',
    'lien-release' => '/liens/lien-release',
    'payment-demand-letter' => '/liens/payment-demand-letter',
]);
