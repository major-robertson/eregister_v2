<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\LlcFormation;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class FormationStats extends Component
{
    private const STATS_TIMEZONE = 'America/New_York';

    public function render(): View
    {
        return view('admin.formation-stats', [
            'revenueStats' => $this->getRevenueStats(),
            'formationStats' => $this->getFormationStats(),
            'recentFormations' => $this->getRecentFormations(),
        ])->layout('layouts.admin', ['title' => 'Formation Stats']);
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
     * Get formation-product revenue (in cents) for different periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getRevenueStats(): array
    {
        return [
            'today' => $this->sumFormationRevenue('today'),
            'yesterday' => $this->sumFormationRevenue('yesterday'),
            'this_week' => $this->sumFormationRevenue('this_week'),
            'this_month' => $this->sumFormationRevenue('this_month'),
        ];
    }

    /**
     * Sum succeeded formation-product payment amounts for an EST period.
     */
    protected function sumFormationRevenue(string $period): int
    {
        return (int) Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->whereHas('price', fn ($query) => $query->where('product_family', 'formation'))
            ->whereBetween('paid_at', $this->getEstDateRange($period))
            ->sum('amount_cents');
    }

    /**
     * Get formation counts (started vs paid) for periods (EST).
     *
     * @return array{
     *     started: array{today: int, yesterday: int, this_week: int, this_month: int},
     *     paid: array{today: int, yesterday: int, this_week: int, this_month: int}
     * }
     */
    protected function getFormationStats(): array
    {
        return [
            'started' => [
                'today' => LlcFormation::whereBetween('created_at', $this->getEstDateRange('today'))->count(),
                'yesterday' => LlcFormation::whereBetween('created_at', $this->getEstDateRange('yesterday'))->count(),
                'this_week' => LlcFormation::whereBetween('created_at', $this->getEstDateRange('this_week'))->count(),
                'this_month' => LlcFormation::whereBetween('created_at', $this->getEstDateRange('this_month'))->count(),
            ],
            'paid' => [
                'today' => LlcFormation::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('today'))->count(),
                'yesterday' => LlcFormation::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('yesterday'))->count(),
                'this_week' => LlcFormation::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('this_week'))->count(),
                'this_month' => LlcFormation::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('this_month'))->count(),
            ],
        ];
    }

    /**
     * Get the last 20 LLC formations with payer and amount info.
     */
    protected function getRecentFormations(): Collection
    {
        return LlcFormation::query()
            // Select only the columns we render. Avoids pulling large JSON
            // columns (core_data, definition_snapshot) into the sort buffer.
            ->select([
                'id', 'business_id', 'selected_states', 'status', 'paid_at', 'created_at',
                'current_phase', 'current_step_key', 'current_state_index',
            ])
            ->with(['business.users', 'payment'])
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function (LlcFormation $formation) {
                $user = $formation->business?->users->first();

                return [
                    'id' => $formation->id,
                    'business' => $formation->business?->name ?? 'Unknown',
                    'name' => $user?->name ?? 'Unknown',
                    'email' => $user?->email ?? 'Unknown',
                    'state' => $formation->selected_states[0] ?? '—',
                    'amount' => $formation->payment?->formattedAmount(),
                    'status' => $formation->display_status,
                    'phase' => $formation->current_phase,
                    'progress' => $this->wizardProgressFor($formation),
                    'paid_at' => $formation->paid_at,
                    'created_at' => $formation->created_at,
                ];
            });
    }

    /**
     * Compute customer-wizard progress as completed steps out of total defined
     * steps. Mirrors the Sales Tax stats computation; works for any form type
     * since it reads the static step lists from the FormRegistry.
     *
     * @return array{done: int, total: int}
     */
    protected function wizardProgressFor(FormApplication $formation): array
    {
        $registry = app(FormRegistry::class);
        $formType = $formation->form_type ?: LlcFormation::FORM_TYPE;

        $coreKeys = array_keys($registry->getBase($formType)['core_steps'] ?? []);
        $coreTotal = count($coreKeys);

        $stateKeysPerState = [];
        foreach ($formation->selected_states ?? [] as $stateCode) {
            $stateSteps = $registry->get($formType, $stateCode)['state_steps'] ?? [];
            unset($stateSteps['state_responsible_people']);
            $stateKeysPerState[] = array_keys($stateSteps);
        }
        $statesTotal = array_sum(array_map('count', $stateKeysPerState));

        $total = $coreTotal + $statesTotal;

        if ($formation->status === 'submitted' || $formation->paid_at !== null) {
            return ['done' => $total, 'total' => $total];
        }

        $done = match ($formation->current_phase) {
            'review' => $total,
            'states' => $this->statePhaseStepsDone($coreTotal, $stateKeysPerState, $formation),
            default => $this->stepIndex($coreKeys, $formation->current_step_key),
        };

        return ['done' => $done, 'total' => $total];
    }

    /**
     * @param  list<list<string>>  $stateKeysPerState
     */
    protected function statePhaseStepsDone(int $coreTotal, array $stateKeysPerState, FormApplication $formation): int
    {
        $done = $coreTotal;
        $currentStateIndex = (int) $formation->current_state_index;

        foreach ($stateKeysPerState as $i => $stepKeys) {
            if ($i < $currentStateIndex) {
                $done += count($stepKeys);
            } elseif ($i === $currentStateIndex) {
                $done += $this->stepIndex($stepKeys, $formation->current_step_key);
            }
        }

        return $done;
    }

    /**
     * @param  list<string>  $keys
     */
    protected function stepIndex(array $keys, ?string $stepKey): int
    {
        $idx = array_search($stepKey, $keys, true);

        return $idx === false ? 0 : $idx;
    }

    public function formatCents(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}
