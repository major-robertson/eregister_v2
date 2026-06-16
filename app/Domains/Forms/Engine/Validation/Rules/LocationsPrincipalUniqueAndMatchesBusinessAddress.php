<?php

namespace App\Domains\Forms\Engine\Validation\Rules;

use App\Domains\Forms\Engine\Validation\CrossFieldValidator;

/**
 * The locations[] repeater must contain exactly one row flagged
 * is_principal, and that row's address must match the application's
 * Principal Business Address (line1/city/state/zip, case-insensitive).
 */
class LocationsPrincipalUniqueAndMatchesBusinessAddress implements CrossFieldValidator
{
    public function name(): string
    {
        return 'locations_principal_unique_and_matches_business_address';
    }

    public function validate(array $data, string $field, array $options = []): array
    {
        $items = data_get($data, $field, []);

        if (! is_array($items) || $items === []) {
            return [];
        }

        $principals = array_values(array_filter(
            $items,
            fn ($item) => is_array($item) && ! empty($item['is_principal'])
        ));

        if (count($principals) !== 1) {
            return [
                $field => ['Exactly one location must be marked as the principal business location.'],
            ];
        }

        $businessAddress = data_get($data, 'business_address', []);
        $principalAddress = $principals[0]['address'] ?? [];

        foreach (['line1', 'city', 'state', 'zip'] as $part) {
            $a = strtolower(trim((string) ($principalAddress[$part] ?? '')));
            $b = strtolower(trim((string) ($businessAddress[$part] ?? '')));

            if ($a !== $b) {
                return [
                    $field => ['The principal location address must match the Principal Business Address entered earlier.'],
                ];
            }
        }

        return [];
    }
}
