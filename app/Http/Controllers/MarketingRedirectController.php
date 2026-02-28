<?php

namespace App\Http\Controllers;

use App\Domains\Marketing\Models\MarketingRedirect;
use App\Domains\Marketing\Models\MarketingRedirectVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarketingRedirectController extends Controller
{
    public function handle(Request $request, string $slug): RedirectResponse
    {
        $redirect = MarketingRedirect::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        MarketingRedirectVisit::create([
            'marketing_redirect_id' => $redirect->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'visited_at' => now(),
        ]);

        return redirect($redirect->getDestinationUrlWithUtm());
    }
}
