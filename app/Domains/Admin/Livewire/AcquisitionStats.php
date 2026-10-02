<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Admin\Reports\AcquisitionReport;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Acquisition section of the stats dashboard: sign-ups, paying businesses
 * and lifetime revenue by first-touch channel and landing page, per month.
 * Rendered inside StatsBoard, which is admin-only.
 */
class AcquisitionStats extends Component
{
    private const MONTHS = 12;

    #[Url(as: 'acq_channel')]
    public string $channel = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }

    public function updatedChannel(): void
    {
        if (! in_array($this->channel, AcquisitionReport::CHANNELS, true)) {
            $this->channel = '';
        }
    }

    public function render(AcquisitionReport $report): View
    {
        $channel = in_array($this->channel, AcquisitionReport::CHANNELS, true) ? $this->channel : null;
        $rows = collect($report->matrix(self::MONTHS, $channel));

        return view('admin.acquisition-stats', [
            'months' => $rows->groupBy('month')->map(fn ($monthRows, string $month) => [
                'label' => CarbonImmutable::createFromFormat('!Y-m', $month)->format('M Y'),
                'rows' => $monthRows->values(),
                'total' => [
                    'signups' => $monthRows->sum('signups'),
                    'paying_businesses' => $monthRows->sum('paying_businesses'),
                    'revenue_cents' => $monthRows->sum('revenue_cents'),
                    'conversion_rate' => $monthRows->sum('signups') > 0
                        ? round($monthRows->sum('converted') / $monthRows->sum('signups') * 100, 1)
                        : 0.0,
                ],
            ]),
            'topPaths' => $report->topLandingPaths(self::MONTHS, $channel, 20),
            'channels' => AcquisitionReport::CHANNELS,
            'since' => $report->windowStart(self::MONTHS),
            'generatedAt' => now(),
        ]);
    }
}
