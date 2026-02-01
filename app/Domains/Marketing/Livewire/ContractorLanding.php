<?php

namespace App\Domains\Marketing\Livewire;

use App\Domains\Marketing\Enums\EventType;
use App\Domains\Marketing\Enums\VisitSource;
use App\Domains\Marketing\Models\MarketingEvent;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use App\Domains\Marketing\Models\MarketingVisit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ContractorLanding extends Component
{
    #[Locked]
    public int $trackingLinkId;

    #[Locked]
    public VisitSource $source;

    #[Locked]
    public string $landingKey = 'liens';

    protected MarketingTrackingLink $trackingLink;

    protected MarketingLead $lead;

    protected ?MarketingVisit $visit = null;

    public function mount(int $trackingLinkId, VisitSource $source): void
    {
        $this->trackingLinkId = $trackingLinkId;
        $this->source = $source;

        // Load models internally
        $this->trackingLink = MarketingTrackingLink::findOrFail($trackingLinkId);
        $this->lead = $this->trackingLink->lead;

        // Determine landing key from campaign
        $this->landingKey = $this->trackingLink->campaign?->landing_key ?? 'liens';

        // Record the visit with explicit source from route
        $this->recordVisit();
    }

    /**
     * Hydrate models on each request (protected properties are not persisted).
     */
    public function hydrate(): void
    {
        $this->trackingLink = MarketingTrackingLink::findOrFail($this->trackingLinkId);
        $this->lead = $this->trackingLink->lead;
    }

    /**
     * Record a visit for analytics.
     */
    protected function recordVisit(): void
    {
        $request = request();

        $this->visit = MarketingVisit::recordFromTrackingLink(
            $this->trackingLink,
            $request->ip(),
            $request->userAgent(),
            $request->header('referer'),
            $this->source
        );
    }

    /**
     * Record a CTA click event.
     */
    public function recordCtaClick(): void
    {
        if ($this->visit) {
            MarketingEvent::recordFromVisit($this->visit, EventType::CtaClick);
        }
    }

    /**
     * Record a call click event.
     */
    public function recordCallClick(): void
    {
        if ($this->visit) {
            MarketingEvent::recordFromVisit($this->visit, EventType::CallClick);
        }
    }

    /**
     * Get the canonical URL for SEO.
     */
    public function getCanonicalUrl(): string
    {
        return route('marketing.landing.slug', ['slug' => $this->lead->slug]);
    }

    public function render(): View
    {
        $view = match ($this->landingKey) {
            'liens' => 'livewire.marketing.contractor-landing',
            default => 'livewire.marketing.contractor-landing',
        };

        return view($view, [
            'lead' => $this->lead,
            'canonicalUrl' => $this->getCanonicalUrl(),
        ]);
    }
}
