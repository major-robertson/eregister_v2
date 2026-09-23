<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Seo\LienStatePage;
use App\Support\Seo\States;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Public "/liens/{state}" pages: mechanics lien deadlines, notice rules, and
 * filing requirements for one state, built from the lien rule reference data.
 * URLs use the full-name slug ("/liens/new-york"); two-letter codes 301 to it.
 */
class LienStateLandingController extends Controller
{
    public function show(string $state): View|RedirectResponse
    {
        $code = States::codeFromSlug($state);
        abort_unless($code, 404);

        $slug = States::slug(States::name($code));
        if ($state !== $slug) {
            return redirect()->route('liens.state', ['state' => $slug], 301);
        }

        $page = Cache::remember("seo.lien-state.v1.{$code}", now()->addDay(), fn () => LienStatePage::forCode($code));
        abort_unless($page, 404);

        return view('pages.liens.lien-state', [
            'page' => $page,
            'nearbyStates' => States::neighbours($code),
        ]);
    }
}
