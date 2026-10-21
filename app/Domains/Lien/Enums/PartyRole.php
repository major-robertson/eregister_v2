<?php

namespace App\Domains\Lien\Enums;

enum PartyRole: string
{
    case Claimant = 'claimant';
    case Customer = 'customer';
    case Owner = 'owner';
    case Gc = 'gc';
    case Subcontractor = 'subcontractor';
    case Lender = 'lender';
    case Tenant = 'tenant';
    case PropertyManager = 'property_manager';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Claimant => 'Claimant (You)',
            self::Customer => 'Customer / Hiring Party',
            self::Owner => 'Property Owner',
            self::Gc => 'General Contractor / Direct Contractor',
            self::Subcontractor => 'Subcontractor',
            self::Lender => 'Construction Lender',
            self::Tenant => 'Tenant / Lessee',
            self::PropertyManager => 'Property manager',
            self::Other => 'Other',
        };
    }

    /**
     * Parties kept for the record only (EREG-135): never the owner, never
     * served, never sent a demand letter.
     */
    public function isInformational(): bool
    {
        return in_array($this, [self::Tenant, self::PropertyManager], true);
    }

    /**
     * The party a project's "who hired you" answer points at.
     */
    public static function forHiredBy(?string $hiredBy): ?self
    {
        return match ($hiredBy) {
            'owner' => self::Owner,
            'direct_contractor' => self::Gc,
            'subcontractor' => self::Subcontractor,
            default => null,
        };
    }

    /**
     * The "who hired you" answer for a hiring party of this role.
     */
    public function hiredBy(): ?string
    {
        return match ($this) {
            self::Owner => 'owner',
            self::Gc => 'direct_contractor',
            self::Subcontractor => 'subcontractor',
            default => null,
        };
    }

    /**
     * How the Parties step names a party of this role in a sentence.
     */
    public function phrase(): string
    {
        return match ($this) {
            self::Claimant => 'claimant',
            self::Customer => 'customer',
            self::Owner => 'property owner',
            self::Gc => 'general contractor',
            self::Subcontractor => 'subcontractor',
            self::Lender => 'construction lender',
            self::Tenant => 'tenant',
            self::PropertyManager => 'property manager',
            self::Other => 'party',
        };
    }
}
