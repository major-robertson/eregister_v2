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
        ['fl', 'Certificate of Registration'],
        ['ca', "Seller's Permit"],
        ['il', 'Certificate of Registration'],
        ['ga', 'Sales and Use Tax Number'],
        ['nj', 'Certificate of Authority'],
        ['pa', 'Sales, Use and Hotel Occupancy Tax License'],
        ['nc', 'Certificate of Registration'],
        ['mi', 'Sales Tax License'],
    ]);

    // Corrected from the EREG-13 research (2026-10-02).
    it('shows the researched Florida, California and Michigan facts', function () {
        $this->get('/lp/sales-tax/fl')
            ->assertOk()
            ->assertSee('Florida <span data-hero-keyword>Certificate of Registration</span>', false)
            ->assertSee('No state fee.')
            ->assertDontSee('$5 by paper');

        $this->get('/lp/sales-tax/ca')
            ->assertOk()
            ->assertSee('The CDTFA says it may be able to issue the permit the same day.');

        $this->get('/lp/sales-tax/mi')
            ->assertOk()
            ->assertSee('Michigan usually processes an online registration within 48 hours; paper applications take 4 to 6 weeks.')
            ->assertDontSee('about 7 business days');
    });

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

    // EREG-116: most resale searchers have no permit yet, so the page asks first.
    it('asks for the permit number first and sends "no" to the registration', function () {
        $this->get('/lp/resale-certificate/tx')
            ->assertOk()
            ->assertSee('Texas Resale Certificate, Signed in Minutes')
            ->assertSee('Do you have a Texas sales tax permit number?')
            ->assertSee('In Texas it is called the Sales and Use Tax Permit.')
            ->assertSeeInOrder(['data-door="sales-tax"', 'No, register me first', 'data-door="resale-cert"', 'Yes, I have one', 'data-door="not-sure"'], false)
            ->assertSee('/register?product=sales-tax&amp;state=TX&amp;intent=resale-certificate', false)
            ->assertSee('/register?product=resale-cert&amp;state=TX', false)
            ->assertSee('/register?product=sales-tax&amp;intent=not-sure&amp;state=TX', false)
            ->assertSee('Not sure if you have one? Help me choose.')
            ->assertSee('A Texas resale certificate needs your Texas sales tax permit number.')
            ->assertDontSeeText('What do you need today?');
    });

    it('shows the permit price before the certificate price', function () {
        $this->get('/lp/resale-certificate/ny')
            ->assertOk()
            ->assertSeeInOrder(['Two flat prices. No per-certificate fees.', 'Need the permit first?', '$199', 'per state', 'Resale certificates', '$297'], false);
    });

    it('renders the generic page and 404s for unknown states', function () {
        $this->get('/lp/resale-certificate')
            ->assertOk()
            ->assertSee('Resale Certificates, Signed in Minutes')
            ->assertSee('Do you have a sales tax permit number?')
            ->assertSee('/register?product=sales-tax&amp;intent=resale-certificate', false);
        $this->get('/lp/resale-certificate/zz')->assertNotFound();
    });

    it('keeps the three doors on the sales tax pages', function () {
        $this->get('/lp/sales-tax/tx')
            ->assertOk()
            ->assertSeeInOrder(['data-door="sales-tax"', 'data-door="resale-cert"', 'data-door="not-sure"'], false)
            ->assertDontSeeText('Do you have a Texas sales tax permit number?')
            ->assertDontSee('intent=resale-certificate', false);
    });
});
