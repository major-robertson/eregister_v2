<?php

namespace App\Domains\Lien\Enums;

enum ClaimantType: string
{
    case Gc = 'gc';
    case Subcontractor = 'subcontractor';
    case SubSubContractor = 'sub_sub_contractor';
    case SupplierToOwner = 'supplier_to_owner';
    case SupplierToContractor = 'supplier_to_contractor';
    case SupplierToSubcontractor = 'supplier_to_subcontractor';
    case Other = 'other';

    /**
     * Derive the canonical claimant type from the two role-capture answers.
     *
     * @param  string  $providedType  'labor' | 'materials_only' | 'both'
     * @param  string  $hiredBy  'owner' | 'direct_contractor' | 'subcontractor'
     *
     * @throws \InvalidArgumentException for any tuple outside the matrix. We do
     *                                   NOT fall back to self::Other — that would silently hide bad input. The
     *                                   form's `in:` rules guarantee only valid tuples reach this method.
     */
    public static function derive(string $providedType, string $hiredBy): self
    {
        return match ([$providedType, $hiredBy]) {
            ['labor', 'owner'], ['both', 'owner'] => self::Gc,
            ['labor', 'direct_contractor'], ['both', 'direct_contractor'] => self::Subcontractor,
            ['labor', 'subcontractor'], ['both', 'subcontractor'] => self::SubSubContractor,
            ['materials_only', 'owner'] => self::SupplierToOwner,
            ['materials_only', 'direct_contractor'] => self::SupplierToContractor,
            ['materials_only', 'subcontractor'] => self::SupplierToSubcontractor,
            default => throw new \InvalidArgumentException(
                "Invalid claimant role facts: provided_type='{$providedType}', hired_by='{$hiredBy}'."
            ),
        };
    }

    /**
     * Reverse-derive the "who hired you" answer for edit prefill.
     * Returns null for Other (no clean mapping).
     */
    public function hiredBy(): ?string
    {
        return match ($this) {
            self::Gc, self::SupplierToOwner => 'owner',
            self::Subcontractor, self::SupplierToContractor => 'direct_contractor',
            self::SubSubContractor, self::SupplierToSubcontractor => 'subcontractor',
            self::Other => null,
        };
    }

    /**
     * Reverse-derive a "what did you provide" answer for edit prefill. The
     * labor-vs-both distinction is not encoded in claimant_type, so contractor
     * tiers fall back to 'both'. Returns null for Other.
     */
    public function providedTypeGuess(): ?string
    {
        if ($this === self::Other) {
            return null;
        }

        return $this->isSupplier() ? 'materials_only' : 'both';
    }

    public function label(): string
    {
        return match ($this) {
            self::Gc => 'General Contractor / Direct Contractor',
            self::Subcontractor => 'Subcontractor',
            self::SubSubContractor => 'Sub-Subcontractor',
            self::SupplierToOwner => 'Supplier to Owner',
            self::SupplierToContractor => 'Supplier to Contractor',
            self::SupplierToSubcontractor => 'Supplier to Subcontractor',
            self::Other => 'Other',
        };
    }

    /**
     * Check if this claimant type is a supplier role.
     */
    public function isSupplier(): bool
    {
        return in_array($this, [
            self::SupplierToOwner,
            self::SupplierToContractor,
            self::SupplierToSubcontractor,
        ], true);
    }

    /**
     * Check if this claimant type requires a GC party.
     */
    public function requiresGcParty(): bool
    {
        return in_array($this, [
            self::Subcontractor,
            self::SubSubContractor,
            self::SupplierToContractor,
            self::SupplierToSubcontractor,
        ], true);
    }

    /**
     * Check if this claimant type requires a subcontractor party.
     */
    public function requiresSubcontractorParty(): bool
    {
        return in_array($this, [
            self::SubSubContractor,
            self::SupplierToSubcontractor,
        ], true);
    }
}
