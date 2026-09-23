<?php

namespace App\Http\Controllers;

use App\Domains\ResaleCert\Seo\ResaleStatePage;
use App\Support\Seo\States;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Public "/resale-certificates/{state}" pages built from the resale rule
 * table. Full-name slugs are canonical; two-letter codes 301 to them.
 */
class ResaleStateLandingController extends Controller
{
    public function show(string $state): View|RedirectResponse
    {
        $states = Cache::remember('seo.resale-states.v1', now()->addDay(), fn () => ResaleStatePage::availableStates());

        $code = States::codeFromSlug($state, $states);
        abort_unless($code, 404);

        $slug = States::slug($states[$code]);
        if ($state !== $slug) {
            return redirect()->route('resale-certificates.state', ['state' => $slug], 301);
        }

        $page = Cache::remember("seo.resale-state.v1.{$code}", now()->addDay(), fn () => ResaleStatePage::forCode($code));
        abort_unless($page, 404);

        return view('pages.resale-state', [
            'page' => $page,
            'nearbyStates' => States::neighbours($code, 4, $states),
        ]);
    }
}
