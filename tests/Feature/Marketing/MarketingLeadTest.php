<?php

use App\Domains\Marketing\Models\MarketingLead;

it('generates a public_id on creation', function () {
    $lead = MarketingLead::create([
        'business_name' => 'Test Contracting LLC',
        'mailing_address' => '123 Main St',
        'mailing_city' => 'Louisville',
        'mailing_state' => 'KY',
        'mailing_zip' => '40258',
    ]);

    expect($lead->public_id)->not->toBeNull();
    expect(strlen($lead->public_id))->toBe(26); // ULID length
});

it('generates a unique slug on creation', function () {
    $lead = MarketingLead::create([
        'business_name' => 'Test Contracting LLC',
    ]);

    expect($lead->slug)->toBe('test-contracting-llc');
});

it('generates unique slugs with suffix for collisions', function () {
    $lead1 = MarketingLead::create([
        'business_name' => 'Test Company',
    ]);

    $lead2 = MarketingLead::create([
        'business_name' => 'Test Company',
    ]);

    $lead3 = MarketingLead::create([
        'business_name' => 'Test Company',
    ]);

    expect($lead1->slug)->toBe('test-company');
    expect($lead2->slug)->toBe('test-company-1');
    expect($lead3->slug)->toBe('test-company-2');
});

it('uses contact_name for slug if business_name is empty', function () {
    $lead = MarketingLead::create([
        'contact_name' => 'John Doe',
    ]);

    expect($lead->slug)->toBe('john-doe');
});

it('returns display name correctly', function () {
    $leadWithBusiness = MarketingLead::create([
        'business_name' => 'Acme Inc',
        'contact_name' => 'John Doe',
    ]);

    $leadWithContact = MarketingLead::create([
        'contact_name' => 'Jane Smith',
    ]);

    $leadWithNeither = MarketingLead::create([
        'mailing_address' => '123 Main St',
    ]);

    expect($leadWithBusiness->display_name)->toBe('Acme Inc');
    expect($leadWithContact->display_name)->toBe('Jane Smith');
    expect($leadWithNeither->display_name)->toBe('Unknown');
});

it('returns full mailing address correctly', function () {
    $lead = MarketingLead::create([
        'business_name' => 'Test Co',
        'mailing_address' => '123 Main St',
        'mailing_address_2' => 'Suite 100',
        'mailing_city' => 'Louisville',
        'mailing_state' => 'KY',
        'mailing_zip' => '40258',
    ]);

    expect($lead->full_mailing_address)->toBe('123 Main St, Suite 100, Louisville, KY 40258');
});

it('can store all permit data fields', function () {
    $lead = MarketingLead::create([
        'business_name' => 'BGIS Integrated Technical Services',
        'mailing_address' => '9942 State Rd 52',
        'mailing_city' => 'Hudson',
        'mailing_state' => 'FL',
        'mailing_zip' => '34667',
        'email' => 'permits@company.com',
        'phone' => '7274570617',
        'permit_or_external_id' => 'COM-HVAC-26-00066',
        'record_id' => '019be4e4-4296-7025-9022-7e8d4cc25e69',
        'record_date' => '2026-01-21',
        'raw_category' => 'HVAC Commercial - Replacement',
        'title' => 'Record COM-HVAC-26-00066:',
        'description' => 'Replacing (2) HVAC Like for Like RTU',
        'estimated_cost' => 25445.00,
        'property_address' => '7528 Dixie Hwy',
        'property_city' => 'Louisville',
        'property_state' => 'KY',
        'property_zip' => '40258',
        'source_url' => 'https://example.com/permit',
        'role' => 'Licensed Professional / HVAC Contractor',
    ]);

    expect($lead->permit_or_external_id)->toBe('COM-HVAC-26-00066');
    expect($lead->estimated_cost)->toBe('25445.00');
    expect($lead->property_address)->toBe('7528 Dixie Hwy');
    expect($lead->role)->toBe('Licensed Professional / HVAC Contractor');
});
