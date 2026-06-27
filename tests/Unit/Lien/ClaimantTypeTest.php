<?php

use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\PartyRole;

it('derives the canonical claimant type from the two role facts', function (string $provided, string $hiredBy, ClaimantType $expected) {
    expect(ClaimantType::derive($provided, $hiredBy))->toBe($expected);
})->with([
    'labor + owner => gc' => ['labor', 'owner', ClaimantType::Gc],
    'both + owner => gc' => ['both', 'owner', ClaimantType::Gc],
    'labor + direct_contractor => subcontractor' => ['labor', 'direct_contractor', ClaimantType::Subcontractor],
    'both + direct_contractor => subcontractor' => ['both', 'direct_contractor', ClaimantType::Subcontractor],
    'labor + subcontractor => sub_sub' => ['labor', 'subcontractor', ClaimantType::SubSubContractor],
    'both + subcontractor => sub_sub' => ['both', 'subcontractor', ClaimantType::SubSubContractor],
    'materials_only + owner => supplier_to_owner' => ['materials_only', 'owner', ClaimantType::SupplierToOwner],
    'materials_only + direct_contractor => supplier_to_contractor' => ['materials_only', 'direct_contractor', ClaimantType::SupplierToContractor],
    'materials_only + subcontractor => supplier_to_subcontractor' => ['materials_only', 'subcontractor', ClaimantType::SupplierToSubcontractor],
]);

it('throws on an invalid role-fact tuple rather than falling back to Other', function () {
    ClaimantType::derive('labor', 'nobody');
})->throws(InvalidArgumentException::class);

it('never derives Other', function () {
    // Sanity: every valid tuple maps to a concrete tier, Other is legacy-only.
    foreach (['labor', 'materials_only', 'both'] as $provided) {
        foreach (['owner', 'direct_contractor', 'subcontractor'] as $hiredBy) {
            expect(ClaimantType::derive($provided, $hiredBy))->not->toBe(ClaimantType::Other);
        }
    }
});

it('reverse-derives the role facts for edit prefill', function (ClaimantType $type, ?string $hiredBy, ?string $provided) {
    expect($type->hiredBy())->toBe($hiredBy);
    expect($type->providedTypeGuess())->toBe($provided);
})->with([
    'gc' => [ClaimantType::Gc, 'owner', 'both'],
    'subcontractor' => [ClaimantType::Subcontractor, 'direct_contractor', 'both'],
    'sub_sub' => [ClaimantType::SubSubContractor, 'subcontractor', 'both'],
    'supplier_to_owner' => [ClaimantType::SupplierToOwner, 'owner', 'materials_only'],
    'supplier_to_contractor' => [ClaimantType::SupplierToContractor, 'direct_contractor', 'materials_only'],
    'supplier_to_subcontractor' => [ClaimantType::SupplierToSubcontractor, 'subcontractor', 'materials_only'],
    'other' => [ClaimantType::Other, null, null],
]);

it('relabels GC to include direct contractor', function () {
    expect(ClaimantType::Gc->label())->toBe('General Contractor / Direct Contractor');
    expect(PartyRole::Gc->label())->toBe('General Contractor / Direct Contractor');
});
