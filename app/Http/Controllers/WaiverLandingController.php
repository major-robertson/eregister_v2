<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Waivers\WaiverStateRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Public marketing pages for lien waivers: the main landing page and the 50
 * per-state SEO pages. Everything is driven by WaiverStateRegistry so the
 * marketing copy can never drift from what the waiver wizard actually
 * generates — a state data file update changes both at once.
 */
class WaiverLandingController extends Controller
{
    /**
     * Main lien waiver landing page: free-generator hero, pricing, and the
     * directory grid linking every per-state page.
     */
    public function index(): View
    {
        return view('pages.liens.lien-waivers', [
            'states' => WaiverStateRegistry::all(),
        ]);
    }

    /**
     * Per-state SEO page. URLs accept 2-letter codes only (full names 404 via
     * the registry lookup); uppercase or mixed-case codes 301 to the lowercase
     * canonical so search engines never index duplicate URLs.
     */
    public function state(string $state): View|RedirectResponse
    {
        abort_unless(WaiverStateRegistry::isSupported($state), 404);

        if ($state !== strtolower($state)) {
            return redirect()->route('liens.lien-waivers.state', ['state' => strtolower($state)], 301);
        }

        $code = strtoupper($state);
        $rules = WaiverStateRegistry::for($code);
        $stateName = $rules['state_name'] ?? WaiverStateRegistry::STATE_NAMES[$code];

        return view('pages.liens.lien-waivers-state', [
            'code' => $code,
            'rules' => $rules,
            'stateName' => $stateName,
            'nearbyStates' => $this->nearbyStates($code),
            'pageTitle' => $stateName.' Lien Waiver Forms | Free '.$stateName.' Lien Waiver Generator',
            'metaDescription' => $this->metaDescription($stateName, $rules),
            'canonicalUrl' => route('liens.lien-waivers.state', ['state' => strtolower($code)]),
        ]);
    }

    /**
     * Unique per-state meta description. States with a prescribed statutory
     * form lead with the statute cite; everyone else leads with the four
     * house forms. Kept under ~165 characters so search results don't
     * truncate mid-clause.
     *
     * @param  array<string, mixed>  $rules
     */
    private function metaDescription(string $stateName, array $rules): string
    {
        if (($rules['compliance_standard'] ?? 'generic') !== 'generic' && ! empty($rules['statute'])) {
            return "Generate {$stateName} lien waiver forms free — the exact statutory text of {$rules['statute']}, plus {$stateName} rules for notarization, witnesses, and e-signature.";
        }

        return "Generate {$stateName} lien waiver forms free — conditional and unconditional waivers for progress and final payments, with {$stateName} signing and e-signature rules.";
    }

    /**
     * Four alphabetical neighbours for the cross-link strip, wrapping at the
     * ends of the registry list so Alabama and Wyoming still get four links.
     *
     * @return array<string, string> code => state name
     */
    private function nearbyStates(string $code): array
    {
        $codes = array_keys(WaiverStateRegistry::STATE_NAMES);
        $count = count($codes);
        $index = (int) array_search($code, $codes, true);

        $nearby = [];

        foreach ([-2, -1, 1, 2] as $offset) {
            $neighbor = $codes[($index + $offset + $count) % $count];
            $nearby[$neighbor] = WaiverStateRegistry::STATE_NAMES[$neighbor];
        }

        return $nearby;
    }
}
