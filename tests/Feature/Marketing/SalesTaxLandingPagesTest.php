<?php

describe('sales tax ads landing pages', function () {
    it('renders a page for each of the ten target states with the state term in the headline', function (string $slug, string $term) {
        $this->get("/lp/sales-tax/{$slug}")
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee($term)
            ->assertSee('per state');
    })->with([
        ['tx', 'Sales and Use Tax Permit'],
        ['ny', 'Certificate of Authority'],
        ['fl', 'Sales Tax Registration'],
        ['ca', "Seller's Permit"],
        ['il', 'Certificate of Registration'],
        ['ga', 'Sales and Use Tax Number'],
        ['nj', 'Certificate of Authority'],
        ['pa', 'Sales, Use and Hotel Occupancy Tax License'],
        ['nc', 'Certificate of Registration'],
        ['mi', 'Sales Tax License'],
    ]);

    it('renders the generic page without a state', function () {
        $this->get('/lp/sales-tax')
            ->assertOk()
            ->assertSee('Filed for You')
            ->assertSee('Start your registration');
    });

    it('uses the ad keyword variant in the headline when one is passed', function () {
        $this->get('/lp/sales-tax/tx?intent=sellers-permit')
            ->assertOk()
            ->assertSee('Texas <span data-hero-keyword>Seller&#039;s Permit</span>', false);
    });

    it('sends the three doors to register with the product, intent and state', function () {
        $this->get('/lp/sales-tax/tx?intent=sales-tax-permit')
            ->assertOk()
            ->assertSee('/register?product=sales-tax&amp;state=TX&amp;intent=sales-tax-permit', false)
            ->assertSee('/register?product=resale-cert&amp;state=TX', false)
            ->assertSee('/register?product=sales-tax&amp;intent=not-sure&amp;state=TX', false)
            ->assertSee('Register for a Texas Sales and Use Tax Permit')
            ->assertSee('Create a Texas resale certificate')
            ->assertSee('Not sure what I need');
    });

    it('falls back to generic copy for a state without its own facts', function () {
        $this->get('/lp/sales-tax/ks')
            ->assertOk()
            ->assertSee('Kansas')
            ->assertSee('Sales Tax Permit');
    });

    it('redirects upper-case slugs to the lower-case page and keeps the query string', function () {
        $this->get('/lp/sales-tax/TX?intent=sellers-permit')
            ->assertRedirect('/lp/sales-tax/tx?intent=sellers-permit');
    });

    it('answers 404 for unknown states and for states with no sales tax', function (string $slug) {
        $this->get("/lp/sales-tax/{$slug}")->assertNotFound();
    })->with(['zz', 'or', 'mt', 'de', 'nh']);
});

describe('resale certificate ads landing pages', function () {
    it('renders a page for each target state naming its resale form', function (string $slug, string $form) {
        $this->get("/lp/resale-certificate/{$slug}")
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee($form)
            ->assertSee('a year');
    })->with([
        ['tx', 'Form 01-339'],
        ['ny', 'Form ST-120'],
        ['fl', 'Form DR-13'],
        ['ca', 'Form CDTFA-230'],
        ['il', 'Form CRT-61'],
        ['ga', 'Form ST-5'],
        ['nj', 'Form ST-3'],
        ['pa', 'Form REV-1220'],
        ['nc', 'Form E-595E'],
        ['mi', 'Form 3372'],
    ]);

    it('puts the certificate door first and keeps registration available', function () {
        $this->get('/lp/resale-certificate/ny')
            ->assertOk()
            ->assertSeeInOrder(['data-door="resale-cert"', 'data-door="sales-tax"', 'data-door="not-sure"'], false)
            ->assertSee('/register?product=resale-cert&amp;state=NY', false)
            ->assertSee('/register?product=sales-tax&amp;state=NY', false);
    });

    it('renders the generic page and 404s for unknown states', function () {
        $this->get('/lp/resale-certificate')->assertOk()->assertSee('Resale Certificates, Signed in Minutes');
        $this->get('/lp/resale-certificate/zz')->assertNotFound();
    });
});
