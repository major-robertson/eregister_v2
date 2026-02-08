<?php

namespace App\Http\Controllers;

use App\Domains\Marketing\Enums\VisitSource;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use App\Domains\Marketing\Models\MarketingVisit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MarketingLandingController extends Controller
{
    /**
     * Handle QR scan landing (source = qr_scan).
     */
    public function tokenLanding(Request $request, string $token): Response
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
    public function slugLanding(Request $request, string $slug): Response
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
    protected function renderLanding(string $landingKey, MarketingTrackingLink $trackingLink): Response
    {
        return match ($landingKey) {
            default => $this->renderLiensLanding($trackingLink),
        };
    }

    /**
     * Render the full liens marketing page with personalized hero CTA.
     * Sets a lead_ref cookie (30 days) for pre-signup continuity.
     */
    protected function renderLiensLanding(MarketingTrackingLink $trackingLink): Response
    {
        $lead = $trackingLink->lead;

        return response(view('liens', [
            'lead' => $lead,
            'canonicalUrl' => route('marketing.landing.slug', ['slug' => $lead->slug]),
            'noIndex' => true,
            'pageTitle' => ($lead->business_name ?? 'Contractor').' - Lien Services',
        ]))->cookie('lead_ref', $lead->public_id, 43200, '/', null, null, true, false, 'Lax');
    }
}
