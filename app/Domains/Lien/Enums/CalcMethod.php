<?php

namespace App\Domains\Lien\Enums;

enum CalcMethod: string
{
    case DaysAfterDate = 'days_after_date';
    case MonthsAfterDate = 'months_after_date';
    case MonthDayAfterMonthOfDate = 'month_day_after_month_of_date';
    case DaysAfterEndOfMonthOfDate = 'days_after_end_of_month_of_date';

    public function label(): string
    {
        return match ($this) {
            self::DaysAfterDate => 'Days After Date',
            self::MonthsAfterDate => 'Months After Date',
            self::MonthDayAfterMonthOfDate => 'Day of Month After Month',
            self::DaysAfterEndOfMonthOfDate => 'Days After End of Month',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::DaysAfterDate => 'Add offset_days to the anchor date',
            self::MonthsAfterDate => 'Add offset_months calendar months to the anchor date',
            self::MonthDayAfterMonthOfDate => 'Go to Nth day of (anchor_month + offset_months)',
            self::DaysAfterEndOfMonthOfDate => 'Go to end of anchor month, then add offset_days',
        };
    }
}
