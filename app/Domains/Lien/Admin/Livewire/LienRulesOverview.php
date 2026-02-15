<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Lien\Models\LienStateRule;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class LienRulesOverview extends Component
{
    #[Url(as: 'state')]
    public string $selectedState = '';

    public function render(): View
    {
        $stateRules = LienStateRule::query()
            ->with(['deadlineRules.documentType'])
            ->orderBy('state')
            ->get();

        return view('lien.admin.rules-overview', [
            'stateRules' => $stateRules,
            'stateList' => $stateRules->pluck('state')->toArray(),
        ])->layout('layouts.admin', ['title' => 'Lien Rules Overview']);
    }

    /**
     * Format a deadline rule into a human-readable string.
     */
    public function formatDeadlineOffset(mixed $rule): string
    {
        $parts = [];

        if ($rule->offset_days) {
            $parts[] = $rule->offset_days.' days';
        }

        if ($rule->offset_months) {
            $parts[] = $rule->offset_months.' months';
        }

        if (empty($parts)) {
            return 'N/A';
        }

        $method = $rule->calc_method?->label() ?? '';
        $trigger = $rule->trigger_event?->label() ?? '';

        return implode(' + ', $parts).($method ? " ({$method})" : '').($trigger ? " from {$trigger}" : '');
    }
}
