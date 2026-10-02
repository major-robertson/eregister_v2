<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Seo\BlankLienDocument;
use App\Domains\Lien\Seo\DeadlineRulesExport;
use App\Domains\Lien\Seo\LienStatePage;
use App\Support\Seo\States;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Public "/liens/{state}" pages: mechanics lien deadlines, notice rules, and
 * filing requirements for one state, built from the lien rule reference data.
 * URLs use the full-name slug ("/liens/new-york"); two-letter codes 301 to it.
 */
class LienStateLandingController extends Controller
{
    public function show(string $state, BlankLienDocument $blanks): View|RedirectResponse
    {
        $code = States::codeFromSlug($state);
        abort_unless($code, 404);

        $slug = States::slug(States::name($code));
        if ($state !== $slug) {
            return redirect()->route('liens.state', ['state' => $slug], 301);
        }

        $page = Cache::remember(LienStatePage::cacheKey($code), now()->addDay(), fn () => LienStatePage::forCode($code));
        abort_unless($page, 404);

        $blank = $blanks->form($code, 'mechanics_lien');

        return view('pages.liens.lien-state', [
            'page' => $page,
            'nearbyStates' => States::bordering($code),
            'calculatorRules' => array_filter([$code => DeadlineRulesExport::cached($code)]),
            'blankClaim' => $blank === null ? null : [
                'url' => route('liens.state.blank-claim', ['state' => $slug]),
                'title' => $blank->title,
            ],
        ]);
    }

    /**
     * Ungated download of the state's mechanics lien instrument with every
     * field blank ("/liens/texas/blank-lien-claim.pdf"). States without a
     * state-specific instrument 404; codes and mixed case 301 to the slug.
     * Not in the sitemap.
     */
    public function blankClaim(string $state, BlankLienDocument $blanks): Response|RedirectResponse
    {
        $code = States::codeFromSlug($state);
        abort_unless($code, 404);

        $slug = States::slug(States::name($code));
        if ($state !== $slug) {
            return redirect()->route('liens.state.blank-claim', ['state' => $slug], 301);
        }

        $form = $blanks->form($code, 'mechanics_lien');
        abort_if($form === null, 404);

        return response($blanks->pdf($code, 'mechanics_lien'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$blanks->filename($form).'"',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /** The free deadline calculator for every state ("/liens/deadline-calculator"). */
    public function calculator(): View
    {
        return view('pages.liens.deadline-calculator', [
            'calculatorRules' => DeadlineRulesExport::allCached(),
        ]);
    }
}
