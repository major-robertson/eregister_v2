<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\SalesTaxRegistration;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SalesTaxStats extends Component
{
    private const STATS_TIMEZONE = 'America/New_York';

    public function render(): View
    {
        return view('admin.sales-tax-stats', [
            'revenueStats' => $this->getRevenueStats(),
            'registrationStats' => $this->getRegistrationStats(),
            'recentRegistrations' => $this->getRecentRegistrations(),
        ])->layout('layouts.admin', ['title' => 'Sales Tax Stats']);
    }

    /**
     * Get UTC date range for a given EST period.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function getEstDateRange(string $period): array
    {
        $now = now(self::STATS_TIMEZONE);

        return match ($period) {
            'today' => [
                $now->copy()->startOfDay()->utc(),
                $now->copy()->endOfDay()->utc(),
            ],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay()->utc(),
                $now->copy()->subDay()->endOfDay()->utc(),
            ],
            'this_week' => [
                $now->copy()->startOfWeek()->utc(),
                $now->copy()->endOfWeek()->utc(),
            ],
            'this_month' => [
                $now->copy()->startOfMonth()->utc(),
                $now->copy()->endOfMonth()->endOfDay()->utc(),
            ],
        };
    }

    /**
     * Get sales-tax-product revenue (in cents) for different periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getRevenueStats(): array
    {
        return [
            'today' => $this->sumTaxRevenue('today'),
            'yesterday' => $this->sumTaxRevenue('yesterday'),
            'this_week' => $this->sumTaxRevenue('this_week'),
            'this_month' => $this->sumTaxRevenue('this_month'),
        ];
    }

    /**
     * Sum succeeded sales-tax-product payment amounts for an EST period.
     */
    protected function sumTaxRevenue(string $period): int
    {
        return (int) Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->whereHas('price', fn ($query) => $query->where('product_family', 'tax'))
            ->whereBetween('paid_at', $this->getEstDateRange($period))
            ->sum('amount_cents');
    }

    /**
     * Get sales tax registration counts (started vs paid) for periods (EST).
     *
     * @return array{
     *     started: array{today: int, yesterday: int, this_week: int, this_month: int},
     *     paid: array{today: int, yesterday: int, this_week: int, this_month: int}
     * }
     */
    protected function getRegistrationStats(): array
    {
        return [
            'started' => [
                'today' => SalesTaxRegistration::whereBetween('created_at', $this->getEstDateRange('today'))->count(),
                'yesterday' => SalesTaxRegistration::whereBetween('created_at', $this->getEstDateRange('yesterday'))->count(),
                'this_week' => SalesTaxRegistration::whereBetween('created_at', $this->getEstDateRange('this_week'))->count(),
                'this_month' => SalesTaxRegistration::whereBetween('created_at', $this->getEstDateRange('this_month'))->count(),
            ],
            'paid' => [
                'today' => SalesTaxRegistration::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('today'))->count(),
                'yesterday' => SalesTaxRegistration::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('yesterday'))->count(),
                'this_week' => SalesTaxRegistration::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('this_week'))->count(),
                'this_month' => SalesTaxRegistration::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('this_month'))->count(),
            ],
        ];
    }

    /**
     * Get the last 20 sales tax registrations with payer and amount info.
     */
    protected function getRecentRegistrations(): Collection
    {
        return SalesTaxRegistration::query()
            // Select only the columns we render. Avoids pulling large JSON
            // columns (core_data, definition_snapshot) into the sort buffer,
            // which can exhaust MySQL's sort memory on big tables.
            ->select([
                'id', 'business_id', 'selected_states', 'status', 'paid_at', 'created_at',
                'current_phase', 'current_step_key', 'current_state_index',
            ])
            ->with(['business.users', 'payment'])
            // Order by the indexed primary key instead of created_at to avoid
            // a filesort; id order matches insertion (recency) order.
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function (SalesTaxRegistration $registration) {
                $user = $registration->business?->users->first();

                return [
                    'id' => $registration->id,
                    'business' => $registration->business?->name ?? 'Unknown',
                    'name' => $user?->name ?? 'Unknown',
                    'email' => $user?->email ?? 'Unknown',
                    'states' => $registration->stateCount(),
                    'amount' => $registration->payment?->formattedAmount(),
                    'status' => $registration->display_status,
                    'phase' => $registration->current_phase,
                    'progress' => $this->wizardProgressFor($registration),
                    'paid_at' => $registration->paid_at,
                    'created_at' => $registration->created_at,
                ];
            });
    }

    /**
     * Compute customer-wizard progress as completed steps out of total
     * defined steps. Uses the static step lists from the FormRegistry plus
     * the saved wizard cursor, so it avoids loading/decrypting per-row form
     * answers. The denominator reflects defined steps (not conditional
     * visibility), which is good enough for an admin overview.
     *
     * @return array{done: int, total: int}
     */
    protected function wizardProgressFor(FormApplication $registration): array
    {
        $registry = app(FormRegistry::class);
        $formType = $registration->form_type ?: SalesTaxRegistration::FORM_TYPE;

        $coreKeys = array_keys($registry->getBase($formType)['core_steps'] ?? []);
        $coreTotal = count($coreKeys);

        $stateKeysPerState = [];
        foreach ($registration->selected_states ?? [] as $stateCode) {
            $stateSteps = $registry->get($formType, $stateCode)['state_steps'] ?? [];
            unset($stateSteps['state_responsible_people']);
            $stateKeysPerState[] = array_keys($stateSteps);
        }
        $statesTotal = array_sum(array_map('count', $stateKeysPerState));

        $total = $coreTotal + $statesTotal;

        // Locked / submitted / paid applications are considered fully done.
        if ($registration->status === 'submitted' || $registration->paid_at !== null) {
            return ['done' => $total, 'total' => $total];
        }

        $done = match ($registration->current_phase) {
            'review' => $total,
            'states' => $this->statePhaseStepsDone($coreTotal, $stateKeysPerState, $registration),
            default => $this->stepIndex($coreKeys, $registration->current_step_key),
        };

        return ['done' => $done, 'total' => $total];
    }

    /**
     * Completed steps when the cursor is in the per-state phase: all core
     * steps, plus every step of fully-passed states, plus the steps before
     * the cursor within the current state.
     *
     * @param  list<list<string>>  $stateKeysPerState
     */
    protected function statePhaseStepsDone(int $coreTotal, array $stateKeysPerState, FormApplication $registration): int
    {
        $done = $coreTotal;
        $currentStateIndex = (int) $registration->current_state_index;

        foreach ($stateKeysPerState as $i => $stepKeys) {
            if ($i < $currentStateIndex) {
                $done += count($stepKeys);
            } elseif ($i === $currentStateIndex) {
                $done += $this->stepIndex($stepKeys, $registration->current_step_key);
            }
        }

        return $done;
    }

    /**
     * Zero-based position of a step key within a list (0 when not found).
     *
     * @param  list<string>  $keys
     */
    protected function stepIndex(array $keys, ?string $stepKey): int
    {
        $idx = array_search($stepKey, $keys, true);

        return $idx === false ? 0 : $idx;
    }

    /**
     * Format an amount in cents as a USD string.
     */
    public function formatCents(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}
