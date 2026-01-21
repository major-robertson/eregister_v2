<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Models\LienProject;

beforeEach(function () {
    $this->business = Business::factory()->create();
});

it('calculates balance due correctly', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'base_contract_amount_cents' => 1000000, // $10,000
        'change_orders_cents' => 50000,          // $500
        'credits_deductions_cents' => 10000,     // $100
        'payments_received_cents' => 300000,     // $3,000
        'uncompleted_work_cents' => 20000,       // $200
    ]);

    // 10000 + 500 - 100 - 3000 - 200 = 7200
    expect($project->balanceDueCents())->toBe(720000);
});

it('returns null for balance due when no base contract', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'base_contract_amount_cents' => null,
        'payments_received_cents' => 300000,
    ]);

    expect($project->balanceDueCents())->toBeNull();
});

it('handles negative change orders', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'base_contract_amount_cents' => 1000000, // $10,000
        'change_orders_cents' => -50000,         // -$500 (deductive change order)
        'credits_deductions_cents' => null,
        'payments_received_cents' => null,
        'uncompleted_work_cents' => null,
    ]);

    // 10000 - 500 = 9500
    expect($project->balanceDueCents())->toBe(950000);
});

it('treats null values as zero in calculations', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'base_contract_amount_cents' => 500000, // $5,000
        'change_orders_cents' => null,
        'credits_deductions_cents' => null,
        'payments_received_cents' => null,
        'uncompleted_work_cents' => null,
    ]);

    expect($project->balanceDueCents())->toBe(500000);
});

it('indicates when project has financial data', function () {
    $projectWithData = LienProject::factory()->forBusiness($this->business)->create([
        'base_contract_amount_cents' => 500000,
    ]);

    $projectWithoutData = LienProject::factory()->forBusiness($this->business)->create([
        'base_contract_amount_cents' => null,
    ]);

    expect($projectWithData->hasFinancialData())->toBeTrue();
    expect($projectWithoutData->hasFinancialData())->toBeFalse();
});

it('formats balance due correctly', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'base_contract_amount_cents' => 1234567, // $12,345.67
    ]);

    expect($project->formattedBalanceDue())->toBe('$12,345.67');
});

it('returns null for formatted balance when no financial data', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'base_contract_amount_cents' => null,
    ]);

    expect($project->formattedBalanceDue())->toBeNull();
});

describe('ClaimantType enum', function () {
    it('has correct labels', function () {
        expect(ClaimantType::Gc->label())->toBe('General Contractor');
        expect(ClaimantType::Subcontractor->label())->toBe('Subcontractor');
        expect(ClaimantType::SubSubContractor->label())->toBe('Sub-Subcontractor');
        expect(ClaimantType::SupplierToOwner->label())->toBe('Supplier to Owner');
        expect(ClaimantType::SupplierToContractor->label())->toBe('Supplier to Contractor');
        expect(ClaimantType::SupplierToSubcontractor->label())->toBe('Supplier to Subcontractor');
    });

    it('identifies supplier roles', function () {
        expect(ClaimantType::SupplierToOwner->isSupplier())->toBeTrue();
        expect(ClaimantType::SupplierToContractor->isSupplier())->toBeTrue();
        expect(ClaimantType::SupplierToSubcontractor->isSupplier())->toBeTrue();
        expect(ClaimantType::Subcontractor->isSupplier())->toBeFalse();
        expect(ClaimantType::Gc->isSupplier())->toBeFalse();
    });

    it('identifies roles requiring GC party', function () {
        expect(ClaimantType::Subcontractor->requiresGcParty())->toBeTrue();
        expect(ClaimantType::SubSubContractor->requiresGcParty())->toBeTrue();
        expect(ClaimantType::SupplierToContractor->requiresGcParty())->toBeTrue();
        expect(ClaimantType::SupplierToSubcontractor->requiresGcParty())->toBeTrue();
        expect(ClaimantType::Gc->requiresGcParty())->toBeFalse();
        expect(ClaimantType::SupplierToOwner->requiresGcParty())->toBeFalse();
    });

    it('identifies roles requiring subcontractor party', function () {
        expect(ClaimantType::SubSubContractor->requiresSubcontractorParty())->toBeTrue();
        expect(ClaimantType::SupplierToSubcontractor->requiresSubcontractorParty())->toBeTrue();
        expect(ClaimantType::Subcontractor->requiresSubcontractorParty())->toBeFalse();
        expect(ClaimantType::Gc->requiresSubcontractorParty())->toBeFalse();
    });
});
