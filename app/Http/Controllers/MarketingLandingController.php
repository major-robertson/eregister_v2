<?php

namespace App\Http\Controllers;

use App\Domains\Marketing\Enums\VisitSource;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use App\Domains\Marketing\Models\MarketingVisit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingLandingController extends Controller
{
    /**
     * Handle QR scan landing (source = qr_scan).
     */
    public function tokenLanding(Request $request, string $token): View
    {
        $trackingLink = MarketingTrackingLink::where('token', $token)->firstOrFail();

        if (! $trackingLink->lead) {
            abort(404);
        }

        $this->recordVisit($request, $trackingLink, VisitSource::QrScan);

        $landingKey = $trackingLink->campaign?->landing_key ?? 'liens';

        return $this->renderLanding($landingKey, $trackingLink);
    }

    /**
     * Handle typed slug landing (source = direct).
     */
    public function slugLanding(Request $request, string $slug): View
    {
        // Slug IS the token for vanity links
        $trackingLink = MarketingTrackingLink::where('token', $slug)->firstOrFail();

        if (! $trackingLink->lead) {
            abort(404);
        }

        $this->recordVisit($request, $trackingLink, VisitSource::Direct);

        $landingKey = $trackingLink->campaign?->landing_key ?? 'liens';

        return $this->renderLanding($landingKey, $trackingLink);
    }

    /**
     * Record a marketing visit for attribution.
     */
    protected function recordVisit(Request $request, MarketingTrackingLink $trackingLink, VisitSource $source): void
    {
        MarketingVisit::recordFromTrackingLink(
            $trackingLink,
            $request->ip(),
            $request->userAgent(),
            $request->header('referer'),
            $source
        );
    }

    /**
     * Render the appropriate landing page based on campaign landing key.
     */
    protected function renderLanding(string $landingKey, MarketingTrackingLink $trackingLink): View
    {
        return match ($landingKey) {
            default => $this->renderLiensLanding($trackingLink),
        };
    }

    /**
     * Render the full liens marketing page with personalized hero CTA.
     */
    protected function renderLiensLanding(MarketingTrackingLink $trackingLink): View
    {
        $lead = $trackingLink->lead;

        return view('liens', [
            'lead' => $lead,
            'canonicalUrl' => route('marketing.landing.slug', ['slug' => $lead->slug]),
            'noIndex' => true,
            'pageTitle' => ($lead->business_name ?? 'Contractor').' - Lien Services',
        ]);
    }
}
