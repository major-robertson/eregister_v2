<?php

use App\Domains\Business\Models\Business;
use App\Models\User;

describe('Business Stripe customer details', function () {
    it('returns business name for stripeName', function () {
        $business = Business::factory()->create(['name' => 'Black Wolf Roofing']);

        expect($business->stripeName())->toBe('Black Wolf Roofing');
    });

    it('falls back to legal_name when name is null', function () {
        $business = Business::factory()->create([
            'name' => null,
            'legal_name' => 'Black Wolf Roofing LLC',
        ]);

        expect($business->stripeName())->toBe('Black Wolf Roofing LLC');
    });

    it('returns null for stripeName when both name and legal_name are null', function () {
        $business = Business::factory()->create([
            'name' => null,
            'legal_name' => null,
        ]);

        expect($business->stripeName())->toBeNull();
    });

    it('returns the first user email for stripeEmail', function () {
        $user = User::factory()->create(['email' => 'hayden@blackwolfroofingco.com']);
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        expect($business->stripeEmail())->toBe('hayden@blackwolfroofingco.com');
    });

    it('returns null for stripeEmail when business has no users', function () {
        $business = Business::factory()->create();

        expect($business->stripeEmail())->toBeNull();
    });
});
