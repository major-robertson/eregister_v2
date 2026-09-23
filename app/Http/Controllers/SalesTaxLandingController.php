<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Ads landing pages for the sales tax permit and the resale certificate
 * generator: one page per state (the ten states the ads target first, then
 * any state with sales tax) plus a generic page, on the slim lp layout.
 * noindex; the SEO pages carry the canonical content.
 *
 * The visitor's state and the ad's keyword variant travel on to /register
 * (product, intent, state) so the sign-up flow opens where the page left off.
 */
class SalesTaxLandingController extends Controller
{
    /** States with no general sales tax: the pages answer 404 for them. */
    private const NO_SALES_TAX = ['DE', 'MT', 'NH', 'OR'];

    public function permit(?string $state = null): View|RedirectResponse
    {
        if ($redirect = $this->canonical('lp.sales-tax', $state)) {
            return $redirect;
        }

        [$code, $stateName, $facts] = $this->stateContext($state);

        $intentMap = [
            'sales-tax-registration' => 'Sales Tax Registration',
            'sales-tax-permit' => 'Sales Tax Permit',
            'sales-tax-id' => 'Sales Tax ID',
            'sellers-permit' => "Seller's Permit",
            'sales-tax-license' => 'Sales Tax License',
            'certificate-of-authority' => 'Certificate of Authority',
        ];
        $intent = (string) request()->query('intent', '');
        $keyword = array_key_exists($intent, $intentMap)
            ? $intentMap[$intent]
            : ($facts['term'] ?? 'Sales Tax Permit');

        return view('pages.sales-tax.lp-permit', [
            'code' => $code,
            'stateName' => $stateName,
            'facts' => $facts,
            'keyword' => $keyword,
            'intent' => array_key_exists($intent, $intentMap) ? $intent : null,
            'price' => $this->permitPrice(),
            'generatorPrice' => $this->generatorPrice(),
            'pageTitle' => $stateName
                ? "{$stateName} {$keyword} | Registered for You in About 10 Minutes"
                : "{$keyword} Registration | Filed for You in Any State",
            'metaDescription' => $stateName
                ? "Get your {$stateName} {$keyword} without the state portal. Answer a few questions, we prepare and file the registration and send you the number. \$199 per state."
                : 'Register for sales tax in any state without the state portals. Answer a few questions, we prepare and file the registration and send you the number. $199 per state.',
        ]);
    }

    public function resale(?string $state = null): View|RedirectResponse
    {
        if ($redirect = $this->canonical('lp.resale-certificate', $state)) {
            return $redirect;
        }

        [$code, $stateName, $facts] = $this->stateContext($state);

        return view('pages.resale-certificates.lp-resale', [
            'code' => $code,
            'stateName' => $stateName,
            'facts' => $facts,
            'price' => $this->generatorPrice(),
            'permitPrice' => $this->permitPrice(),
            'pageTitle' => $stateName
                ? "{$stateName} Resale Certificate | Signed and Vendor-Ready in Minutes"
                : 'Resale Certificate Generator | Signed Certificates for Every State',
            'metaDescription' => $stateName
                ? "Generate a signed {$stateName} resale certificate on the official state form in minutes. Unlimited certificates for every state you buy in, \$297 a year. No permit yet? We register you first."
                : 'Generate signed resale certificates on the official state forms in minutes. Unlimited certificates for every state you buy in, $297 a year. No permit yet? We register you first.',
        ]);
    }

    /**
     * Unknown states 404; upper-case slugs redirect to the lower-case
     * canonical so an ad URL typed either way lands on one page.
     */
    private function canonical(string $route, ?string $state): ?RedirectResponse
    {
        if ($state === null) {
            return null;
        }

        $code = strtoupper($state);

        abort_unless(array_key_exists($code, config('states')) && ! in_array($code, self::NO_SALES_TAX, true), 404);

        if ($state !== strtolower($state)) {
            return redirect()->route($route, ['state' => strtolower($state)] + request()->query(), 301);
        }

        return null;
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: array<string, string>}
     */
    private function stateContext(?string $state): array
    {
        $code = $state !== null ? strtoupper($state) : null;
        $stateName = $code !== null ? config("states.{$code}") : null;
        $facts = $code !== null ? (config("sales_tax_states.{$code}") ?? []) : [];

        return [$code, $stateName, $facts];
    }

    private function permitPrice(): string
    {
        try {
            return '$'.number_format(Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time')->amount_cents / 100);
        } catch (\Throwable) {
            return '$199';
        }
    }

    private function generatorPrice(): string
    {
        try {
            return '$'.number_format(Price::resolve(
                config('resale_cert.price_family'),
                config('resale_cert.price_key'),
                'default',
                'subscription',
            )->amount_cents / 100);
        } catch (\Throwable) {
            return '$297';
        }
    }
}
