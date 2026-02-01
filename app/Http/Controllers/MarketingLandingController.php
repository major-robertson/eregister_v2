<?php

namespace App\Http\Controllers;

use App\Domains\Marketing\Enums\VisitSource;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use Illuminate\View\View;

class MarketingLandingController extends Controller
{
    /**
     * Handle QR scan landing (source = qr_scan).
     */
    public function tokenLanding(string $token): View
    {
        $trackingLink = MarketingTrackingLink::where('token', $token)->firstOrFail();

        if (! $trackingLink->lead) {
            abort(404);
        }

        return view('marketing.landing', [
            'trackingLinkId' => $trackingLink->id,
            'source' => VisitSource::QrScan,
            'canonicalUrl' => route('marketing.landing.slug', ['slug' => $trackingLink->lead->slug]),
            'businessName' => $trackingLink->lead->business_name ?? 'Contractor',
        ]);
    }

    /**
     * Handle typed slug landing (source = direct).
     */
    public function slugLanding(string $slug): View
    {
        // Slug IS the token for vanity links
        $trackingLink = MarketingTrackingLink::where('token', $slug)->firstOrFail();

        if (! $trackingLink->lead) {
            abort(404);
        }

        return view('marketing.landing', [
            'trackingLinkId' => $trackingLink->id,
            'source' => VisitSource::Direct,
            'canonicalUrl' => route('marketing.landing.slug', ['slug' => $trackingLink->lead->slug]),
            'businessName' => $trackingLink->lead->business_name ?? 'Contractor',
        ]);
    }
}
