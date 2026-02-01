<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Marketing\Enums\LeadCampaignStatus;
use App\Domains\Marketing\Enums\MailingStatus;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingEvent;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use App\Domains\Marketing\Models\MarketingMailing;
use App\Domains\Marketing\Models\MarketingVisit;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class MarketingStats extends Component
{
    public function render(): View
    {
        return view('admin.marketing-stats', [
            'leadStats' => $this->getLeadStats(),
            'visitStats' => $this->getVisitStats(),
            'mailingStats' => $this->getMailingStats(),
            'eventStats' => $this->getEventStats(),
            'campaigns' => $this->getCampaigns(),
            'recentVisits' => $this->getRecentVisits(),
            'recentEvents' => $this->getRecentEvents(),
        ])->layout('layouts.admin', ['title' => 'Marketing Stats']);
    }

    /**
     * Get lead counts for different time periods.
     *
     * @return array{total: int, today: int, this_week: int, this_month: int}
     */
    protected function getLeadStats(): array
    {
        return [
            'total' => MarketingLead::count(),
            'today' => MarketingLead::whereDate('created_at', today())->count(),
            'this_week' => MarketingLead::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => MarketingLead::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];
    }

    /**
     * Get visit counts for different time periods.
     *
     * @return array{total: int, today: int, this_week: int, this_month: int, qr_scans: int}
     */
    protected function getVisitStats(): array
    {
        return [
            'total' => MarketingVisit::count(),
            'today' => MarketingVisit::whereDate('visited_at', today())->count(),
            'this_week' => MarketingVisit::whereBetween('visited_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => MarketingVisit::whereBetween('visited_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'qr_scans' => MarketingVisit::where('source', 'qr_scan')->count(),
        ];
    }

    /**
     * Get mailing counts for different statuses.
     *
     * @return array{total: int, executed: int, delivered: int, failed: int, pending: int}
     */
    protected function getMailingStats(): array
    {
        return [
            'total' => MarketingMailing::count(),
            'executed' => MarketingMailing::whereNotNull('executed_at')->count(),
            'delivered' => MarketingMailing::where('provider_status', MailingStatus::Completed)->count(),
            'failed' => MarketingMailing::whereNotNull('failed_at')->count(),
            'pending' => MarketingMailing::whereNull('executed_at')->whereNull('failed_at')->count(),
        ];
    }

    /**
     * Get event counts for different time periods.
     *
     * @return array{total: int, today: int, this_week: int, cta_clicks: int}
     */
    protected function getEventStats(): array
    {
        return [
            'total' => MarketingEvent::count(),
            'today' => MarketingEvent::whereDate('occurred_at', today())->count(),
            'this_week' => MarketingEvent::whereBetween('occurred_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'cta_clicks' => MarketingEvent::where('event_type', 'cta_click')->count(),
        ];
    }

    /**
     * Get campaign summary.
     */
    protected function getCampaigns(): Collection
    {
        return MarketingCampaign::query()
            ->withCount(['leadCampaigns', 'steps'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (MarketingCampaign $campaign) {
                $enrollments = MarketingLeadCampaign::where('campaign_id', $campaign->id);

                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                    'steps_count' => $campaign->steps_count,
                    'total_enrolled' => $campaign->lead_campaigns_count,
                    'completed' => (clone $enrollments)->where('status', LeadCampaignStatus::Completed)->count(),
                    'in_progress' => (clone $enrollments)->where('status', LeadCampaignStatus::InProgress)->count(),
                    'failed' => (clone $enrollments)->where('status', LeadCampaignStatus::Failed)->count(),
                ];
            });
    }

    /**
     * Get the last 20 visits.
     */
    protected function getRecentVisits(): Collection
    {
        return MarketingVisit::query()
            ->with(['lead', 'trackingLink.campaignStep'])
            ->latest('visited_at')
            ->limit(20)
            ->get()
            ->map(function (MarketingVisit $visit) {
                return [
                    'id' => $visit->id,
                    'lead_name' => $visit->lead?->display_name ?? 'Unknown',
                    'source' => $visit->source->label(),
                    'step_name' => $visit->trackingLink?->campaignStep?->name ?? '-',
                    'visited_at' => $visit->visited_at,
                ];
            });
    }

    /**
     * Get the last 20 events.
     */
    protected function getRecentEvents(): Collection
    {
        return MarketingEvent::query()
            ->with(['lead', 'campaignStep'])
            ->latest('occurred_at')
            ->limit(20)
            ->get()
            ->map(function (MarketingEvent $event) {
                return [
                    'id' => $event->id,
                    'lead_name' => $event->lead?->display_name ?? 'Unknown',
                    'event_type' => $event->event_type->label(),
                    'step_name' => $event->campaignStep?->name ?? '-',
                    'occurred_at' => $event->occurred_at,
                ];
            });
    }
}
