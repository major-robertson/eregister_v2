<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Waivers\WaiverBlankForms;
use App\Domains\Lien\Waivers\WaiverFormPreview;
use App\Domains\Lien\Waivers\WaiverIntent;
use App\Domains\Lien\Waivers\WaiverStateFaq;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Support\Seo\States;
use App\Support\Seo\Text;
use App\Support\Seo\Urls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Public marketing pages for lien waivers: the main landing page, the 50
 * per-state SEO pages, and the ads landing pages. Everything is driven by
 * WaiverStateRegistry so the marketing copy can never drift from what the
 * waiver wizard actually generates — a state data file update changes both
 * at once.
 */
class WaiverLandingController extends Controller
{
    /**
     * Main lien waiver landing page: free-generator hero, pricing, and the
     * directory grid linking every per-state page.
     */
    public function index(WaiverBlankForms $blanks): View
    {
        return view('pages.liens.lien-waivers', [
            'states' => WaiverStateRegistry::all(),
            'blankForms' => $blanks->all(),
        ]);
    }

    /**
     * Per-state SEO page. URLs accept 2-letter codes only (full names 404 via
     * the registry lookup); uppercase or mixed-case codes 301 to the lowercase
     * canonical so search engines never index duplicate URLs.
     */
    public function state(string $state, WaiverBlankForms $blanks): View|RedirectResponse
    {
        abort_unless(WaiverStateRegistry::isSupported($state), 404);

        if ($state !== strtolower($state)) {
            return redirect()->route('liens.lien-waivers.state', ['state' => strtolower($state)], 301);
        }

        $code = strtoupper($state);
        $rules = WaiverStateRegistry::for($code);
        $stateName = $rules['state_name'] ?? WaiverStateRegistry::STATE_NAMES[$code];
        $blankForms = $blanks->forState($code);

        return view('pages.liens.lien-waivers-state', [
            'code' => $code,
            'rules' => $rules,
            'stateName' => $stateName,
            'article' => Text::article($stateName),
            'nearbyStates' => $this->nearbyStates($code),
            'blankForms' => $blankForms,
            'faq' => WaiverStateFaq::for($rules, $blankForms),
            // Under 60 characters for every state (North Carolina = 49), so
            // Google shows the whole title instead of rewriting it.
            'pageTitle' => $stateName.' Lien Waiver Forms (Free Generator)',
            'metaDescription' => $this->metaDescription($stateName, $rules),
            'canonicalUrl' => Urls::absolute(route('liens.lien-waivers.state', ['state' => strtolower($code)], absolute: false)),
        ]);
    }

    /**
     * Ungated download of a blank statutory waiver form. Only states whose
     * law prescribes the wording have blanks; every other state, and any
     * kind the state does not use, 404s. Not in the sitemap.
     */
    public function blank(string $state, string $kind, WaiverBlankForms $blanks): Response|RedirectResponse
    {
        abort_unless(WaiverStateRegistry::isSupported($state), 404);

        if ($state !== strtolower($state)) {
            return redirect()->route('liens.lien-waivers.blank', ['state' => strtolower($state), 'kind' => $kind], 301);
        }

        $pdf = $blanks->pdf($state, $kind);

        abort_if($pdf === null, 404);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$blanks->filename($state, $kind).'"',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Ads landing page: the state page's promise with no site chrome and the
     * starter above the fold. The state is optional (the generic page speaks
     * for all 50); ?d= and ?type= pre-select the starter so an ad group can
     * land on the choice its keywords imply. noindex — the SEO pages carry
     * the canonical content.
     */
    public function lp(?string $state = null): View|RedirectResponse
    {
        if ($state !== null) {
            abort_unless(WaiverStateRegistry::isSupported($state), 404);

            if ($state !== strtolower($state)) {
                return redirect()->route('lp.lien-waiver', ['state' => strtolower($state)] + request()->query(), 301);
            }
        }

        $code = $state !== null ? strtoupper($state) : null;
        $rules = $code !== null ? WaiverStateRegistry::for($code) : null;
        $stateName = $code !== null ? ($rules['state_name'] ?? WaiverStateRegistry::STATE_NAMES[$code]) : null;

        $preselected = WaiverIntent::normalize([
            'direction' => request()->query('d'),
            'kind' => request()->query('type'),
        ]);

        return view('pages.liens.lien-waiver-lp', [
            'variant' => 'form',
            'code' => $code,
            'rules' => $rules,
            'stateName' => $stateName,
            'previews' => app(WaiverFormPreview::class)->for($code),
            'preselectedDirection' => $preselected['direction'],
            'preselectedKind' => $preselected['kind'],
            'pageTitle' => $stateName !== null
                ? "Free {$stateName} Lien Waiver Form | Generate & Download"
                : 'Free Lien Waiver Form Generator | All 50 States',
            'metaDescription' => $stateName !== null
                ? $this->metaDescription($stateName, $rules)
                : 'Generate the correct lien waiver form for any state, filled in with your details, and download the PDF free. Conditional, unconditional, progress, and final waivers.',
        ]);
    }

    /**
     * Ads landing page for people searching for waiver software: contractors
     * and owners who collect waivers from subs and vendors on every draw.
     * Same page and starter as lp(), but the promise is the paid product
     * (send, remind, track, store) with its price in plain sight, and the
     * starter opens on "I'm paying someone" (collect).
     */
    public function lpSoftware(): View
    {
        return view('pages.liens.lien-waiver-lp', [
            'variant' => 'software',
            'code' => null,
            'rules' => null,
            'stateName' => null,
            'previews' => app(WaiverFormPreview::class)->for(null),
            'preselectedDirection' => 'collect',
            'preselectedKind' => WaiverIntent::normalize(['kind' => request()->query('type')])['kind'],
            'pageTitle' => 'Lien Waiver Software | Collect, Track and Store Signed Waivers',
            'metaDescription' => 'Send lien waivers to subs and vendors for e-signature, get automatic reminders until they sign, and keep every signed copy with the project. $49 a month per person.',
        ]);
    }

    /**
     * Unique per-state meta description. States with a prescribed statutory
     * form lead with the statute cite; everyone else leads with the four
     * house forms. Long state names and statute cites would overrun 160
     * characters, so the first variant that fits wins instead of cutting
     * the sentence mid-clause.
     *
     * @param  array<string, mixed>  $rules
     */
    private function metaDescription(string $stateName, array $rules): string
    {
        if (($rules['compliance_standard'] ?? 'generic') !== 'generic' && ! empty($rules['statute'])) {
            $statute = $rules['statute'];
            $variants = [
                "Generate {$stateName} lien waiver forms free — the exact statutory text of {$statute}, plus {$stateName} rules for notarization, witnesses, and e-signature.",
                "Generate {$stateName} lien waiver forms free — the exact statutory text of {$statute}, plus {$stateName} signing rules.",
                "Generate {$stateName} lien waiver forms free, with the exact statutory text of {$statute}.",
            ];
        } else {
            $variants = [
                "Generate {$stateName} lien waiver forms free — conditional and unconditional waivers for progress and final payments, with {$stateName} signing and e-signature rules.",
                "Generate {$stateName} lien waiver forms free — conditional and unconditional waivers for progress and final payments, with {$stateName} signing rules.",
                "Generate {$stateName} lien waiver forms free: conditional and unconditional, progress and final.",
            ];
        }

        foreach ($variants as $variant) {
            if (mb_strlen($variant) <= 160) {
                return $variant;
            }
        }

        return end($variants);
    }

    /**
     * Bordering states for the cross-link strip (Texas points at Oklahoma
     * and Louisiana), padded to at least four from the alphabetical
     * neighbours, matching the mechanics lien state pages.
     *
     * @return array<string, string> code => state name
     */
    private function nearbyStates(string $code): array
    {
        return States::bordering($code, 4, WaiverStateRegistry::STATE_NAMES);
    }
}
