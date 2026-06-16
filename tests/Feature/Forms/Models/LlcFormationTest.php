<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\LlcFormation;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Child Model Test Co',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
});

it('applies a global scope filtering to llc only', function () {
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
    expect(LlcFormation::count())->toBe(1);
    expect(LlcFormation::first()->form_type)->toBe('llc');
});

it('defaults form_type to llc on create', function () {
    $formation = LlcFormation::create([
        'business_id' => $this->business->id,
        'definition_version' => 1,
        'selected_states' => ['DE'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    expect($formation->form_type)->toBe('llc');
});

it('uses form_application as morph class to stay consistent with the base alias', function () {
    $formation = LlcFormation::create([
        'business_id' => $this->business->id,
        'definition_version' => 1,
        'selected_states' => ['DE'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    expect($formation->getMorphClass())->toBe('form_application');
});
