<?php

namespace App\Domains\Formations\Services;

use Carbon\CarbonInterface;

/**
 * Resolves which recurring (year 2+) state fee components are due at a given
 * LLC membership renewal. Pure/config-driven (reads config/formation_fees.php)
 * so it is fully unit-testable without Stripe.
 *
 * A component is owed at a renewal cycle when:
 *   charge_mode === 'auto'
 *   AND cycleNumber >= first_cycle_due
 *   AND (cycleNumber - first_cycle_due) % interval_years === 0
 *
 * cycleNumber is the renewal index: ~12 months after formation = 1, ~24 = 2.
 */
class FormationFeeSchedule
{
    /**
     * All configured ongoing components for a state (auto + manual).
     *
     * @return array<int, array<string, mixed>>
     */
    public function componentsFor(string $state): array
    {
        return config('formation_fees.'.$state, []);
    }

    /**
     * The auto-charged components owed at the given renewal cycle.
     *
     * @return array<int, array{component_key: string, label: string, amount_cents: int}>
     */
    public function dueCharges(string $state, int $cycleNumber): array
    {
        $due = [];

        foreach ($this->componentsFor($state) as $component) {
            if (($component['charge_mode'] ?? 'auto') !== 'auto') {
                continue;
            }

            $amount = $component['amount_cents'] ?? null;
            if ($amount === null) {
                continue;
            }

            $firstCycle = (int) ($component['first_cycle_due'] ?? 1);
            $interval = (int) ($component['interval_years'] ?? 1);

            if ($interval < 1 || $cycleNumber < $firstCycle) {
                continue;
            }

            if ((($cycleNumber - $firstCycle) % $interval) !== 0) {
                continue;
            }

            $due[] = [
                'component_key' => $component['component_key'],
                'label' => $component['label'],
                'amount_cents' => (int) $amount,
            ];
        }

        return $due;
    }

    /**
     * Renewal index from the subscription anchor to the invoice period start.
     * Identical for the invoice.upcoming and invoice.created events of the
     * same renewal, so the ledger / idempotency key matches across both.
     */
    public function cycleNumberFor(CarbonInterface $subscriptionStart, CarbonInterface $invoicePeriodStart): int
    {
        return (int) floor(abs($subscriptionStart->diffInYears($invoicePeriodStart)));
    }
}
