<?php

namespace App\Domains\Forms\Engine\Validation\Rules;

use App\Domains\Forms\Engine\Validation\CrossFieldValidator;

class OwnershipTotals100 implements CrossFieldValidator
{
    public function name(): string
    {
        return 'ownership_totals_100';
    }

    public function validate(array $data, string $field, array $options = []): array
    {
        $items = data_get($data, $field, []);

        if (! is_array($items) || empty($items)) {
            return [];
        }

        $total = 0;
        foreach ($items as $item) {
            $percent = $item['ownership_percent'] ?? 0;
            $total += (float) $percent;
        }

        // Allow for floating point imprecision
        if (abs($total - 100) > 0.01) {
            return [
                $field => ["Ownership percentages must total 100%. Current total: {$total}%"],
            ];
        }

        return [];
    }
}
