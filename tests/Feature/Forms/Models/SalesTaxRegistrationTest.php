<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\SalesTaxRegistration;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Child Model Test Co',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
});

it('applies a global scope filtering to sales_tax_permit only', function () {
    FormApplication::create([
        'business_id' => $this->business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    FormApplication::create([
        'business_id' => $this->business->id,
        'form_type' => 'llc',
        'definition_version' => 1,
        'selected_states' => ['DE'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    expect(FormApplication::count())->toBe(2);
    expect(SalesTaxRegistration::count())->toBe(1);
    expect(SalesTaxRegistration::first()->form_type)->toBe('sales_tax_permit');
});

it('defaults form_type to sales_tax_permit on create', function () {
    $registration = SalesTaxRegistration::create([
        'business_id' => $this->business->id,
        'definition_version' => 1,
        'selected_states' => ['CA'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    expect($registration->form_type)->toBe('sales_tax_permit');
});

it('uses form_application as morph class to stay consistent with the base alias', function () {
    $registration = SalesTaxRegistration::create([
        'business_id' => $this->business->id,
        'definition_version' => 1,
        'selected_states' => ['CA'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    expect($registration->getMorphClass())->toBe('form_application');
});
