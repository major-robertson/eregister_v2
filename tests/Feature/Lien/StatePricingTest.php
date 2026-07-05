<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Models\Price;
use App\Models\User;
use Database\Seeders\PriceSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

/**
 * Build a lien project (with generated deadlines), an owner party, and a
 * draft mechanics-lien filing for the given state. Mirrors the helper in
 * AttorneyReferralRestrictionTest but scoped to this file.
 */
function makeMechanicsLienFilingForState(object $context, string $state, string $serviceLevel = 'full_service'): void
{
    $context->project = LienProject::factory()->forBusiness($context->business)->create([
        'jobsite_state' => $state,
        'first_furnish_date' => now()->subDays(30),
        'last_furnish_date' => now()->subDays(5),
        'completion_date' => now()->subDays(3),
        'property_class' => 'residential',
    ]);

    app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($context->project);

    LienParty::create([
        'business_id' => $context->business->id,
        'project_id' => $context->project->id,
        'role' => 'owner',
        'name' => 'Test Owner',
        'address1' => '123 Main St',
        'city' => 'Anytown',
        'state' => $state,
        'zip' => '12345',
    ]);

    $context->lienDeadline = $context->project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    $context->filing = LienFiling::factory()->forProject($context->project)->create([
        'document_type_id' => $context->lienDeadline->document_type_id,
        'project_deadline_id' => $context->lienDeadline->id,
        'service_level' => $serviceLevel,
        'jurisdiction_state' => $state,
        'status' => FilingStatus::Draft,
        'created_by_user_id' => $context->user->id,
    ]);
}

describe('Price::resolveLien state overrides', function () {
    beforeEach(function () {
        (new PriceSeeder)->run();
    });

    it('returns the NJ override for a full-service mechanics lien', function () {
        expect(Price::resolveLien('mechanics_lien', 'full_service', 'NJ')->amount_cents)
            ->toBe(89900);
    });

    it('falls back to the default price for a non-overridden state', function () {
        expect(Price::resolveLien('mechanics_lien', 'full_service', 'AZ')->amount_cents)
            ->toBe(29900);
    });

    it('falls back to the default for an overridden state but non-overridden tier', function () {
        // NJ only overrides full_service, so self_serve keeps the default $99.
        expect(Price::resolveLien('mechanics_lien', 'self_serve', 'NJ')->amount_cents)
            ->toBe(9900);
    });

    it('normalizes lowercase state codes', function () {
        expect(Price::resolveLien('mechanics_lien', 'full_service', 'nj')->amount_cents)
            ->toBe(89900);
    });

    it('falls back to the default when no state is given', function () {
        expect(Price::resolveLien('mechanics_lien', 'full_service', null)->amount_cents)
            ->toBe(29900);
    });
});

describe('PriceSeeder state pricing', function () {
    it('seeds the NJ full-service mechanics-lien override row', function () {
        // Clear any migration-landed row, then let the seeder rebuild it from config.
        Price::query()->where('product_family', 'lien')->delete();

        (new PriceSeeder)->run();

        $row = Price::where('product_family', 'lien')
            ->where('product_key', 'mechanics_lien')
            ->where('variant_key', 'NJ_full_service')
            ->where('billing_type', 'one_time')
            ->first();

        expect($row)->not->toBeNull()
            ->and($row->amount_cents)->toBe(89900)
            ->and($row->active)->toBeTrue();
    });
});

describe('checkout resolves the NJ amount for a real filing', function () {
    it('resolves 89900 for an NJ full-service mechanics-lien filing', function () {
        (new PriceSeeder)->run();

        makeMechanicsLienFilingForState($this, 'NJ');

        $amount = Price::resolveLien(
            $this->filing->documentType->slug,
            $this->filing->service_level->value,
            $this->filing->jurisdiction_state,
        )->amount_cents;

        expect($amount)->toBe(89900);
    });
});

describe('wizard displays state-aware pricing', function () {
    it('shows $899.00 for a New Jersey mechanics lien', function () {
        makeMechanicsLienFilingForState($this, 'NJ');

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->assertSee('Choose Your Service Level')
            ->assertSee('$899.00')
            ->assertDontSee('$299.00');
    });

    it('shows the default $299.00 for a non-NJ mechanics lien', function () {
        makeMechanicsLienFilingForState($this, 'AZ');

        Livewire::test(FilingWizard::class, [
            'project' => $this->project,
            'deadline' => $this->lienDeadline,
        ])
            ->set('step', 4)
            ->assertSee('Choose Your Service Level')
            ->assertSee('$299.00')
            ->assertDontSee('$899.00');
    });
});
