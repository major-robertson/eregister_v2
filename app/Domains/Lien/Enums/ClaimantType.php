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

    public function label(): string
    {
        return match ($this) {
            self::Gc => 'General Contractor',
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
