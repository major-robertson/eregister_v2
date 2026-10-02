<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Models\LienStateRule;
use App\Domains\ResaleCert\Seo\ResaleStatePage;
use App\Domains\SalesTax\Seo\SalesTaxStatePage;
use App\Support\Seo\States;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Public "/sales-tax-registration/{state}" pages built from the researched
 * sales tax content files (45 states with a statewide sales tax, plus DC).
 * Full-name slugs are canonical; codes and wrong-case slugs 301 to them.
 */
class SalesTaxStateLandingController extends Controller
{
    public function show(string $state): View|RedirectResponse
    {
        $states = Cache::remember(SalesTaxStatePage::statesCacheKey(), now()->addDay(), fn () => SalesTaxStatePage::availableStates());

        $code = States::codeFromSlug($state, $states);
        abort_unless($code, 404);

        $slug = States::slug($states[$code]);
        if ($state !== $slug) {
            return redirect()->route('sales-tax-registration.state', ['state' => $slug], 301);
        }

        $page = Cache::remember(SalesTaxStatePage::cacheKey($code), now()->addDay(), fn () => SalesTaxStatePage::forCode($code));
        abort_unless($page, 404);

        $resaleStates = Cache::remember(ResaleStatePage::statesCacheKey(), now()->addDay(), fn () => ResaleStatePage::availableStates());

        return view('pages.sales-tax-state', [
            'page' => $page,
            'nearbyStates' => States::bordering($code, 6, $states),
            'resaleUrl' => isset($resaleStates[$code])
                ? route('resale-certificates.state', ['state' => States::slug($resaleStates[$code])])
                : null,
            'lienUrl' => States::name($code) !== null && LienStateRule::query()->whereKey($code)->exists()
                ? route('liens.state', ['state' => $page->slug])
                : null,
        ]);
    }
}
