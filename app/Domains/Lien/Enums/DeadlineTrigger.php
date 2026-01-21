<?php

namespace App\Domains\Lien\Enums;

enum DeadlineTrigger: string
{
    case FirstFurnish = 'first_furnish_date';
    case LastFurnish = 'last_furnish_date';
    case Completion = 'completion_date';
    case NocRecorded = 'noc_recorded_date';
    case ContractDate = 'contract_date';

    public function label(): string
    {
        return match ($this) {
            self::FirstFurnish => 'First Furnishing Date',
            self::LastFurnish => 'Last Furnishing Date',
            self::Completion => 'Completion Date',
            self::NocRecorded => 'Notice of Completion Recorded',
            self::ContractDate => 'Contract Date',
        };
    }
}
