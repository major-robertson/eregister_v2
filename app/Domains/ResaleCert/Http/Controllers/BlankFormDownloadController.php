<?php

namespace App\Domains\ResaleCert\Http\Controllers;

use App\Domains\Forms\Models\SalesTaxRegistration;
use App\Domains\ResaleCert\Models\ResaleStateRule;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Free blank resale certificate forms for paying sales-tax customers —
 * ported perk from the original TaxResaleCertificate app. The filled,
 * auto-generated version is the paid Resale Certificate Generator product.
 */
class BlankFormDownloadController
{
    /**
     * FL and ME blanks are state-issued documents we can't distribute.
     */
    public const EXCLUDED_STATES = ['FL', 'ME'];

    public function __invoke(Request $request, string $state): BinaryFileResponse
    {
        $state = strtoupper($state);
        $business = $request->user()?->currentBusiness();

        abort_unless($business, 403);

        // Free for businesses with a PAID sales tax registration covering
        // this state.
        $hasPaidRegistration = SalesTaxRegistration::query()
            ->where('business_id', $business->id)
            ->whereNotNull('paid_at')
            ->whereJsonContains('selected_states', $state)
            ->exists();

        abort_unless($hasPaidRegistration, 403, 'Blank forms are available after completing a sales tax registration.');

        $template = self::templateFor($state);

        abort_unless($template, 404, 'No blank certificate is available for this state.');

        $path = resource_path(config('resale_cert.templates_path').'/'.$template);

        abort_unless(file_exists($path), 404);

        return response()->download($path, "{$state}_Blank_Resale_Certificate.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * The blank template filename for a state, or null when none can be
     * offered: the state's own form when one exists, else the MTC uniform
     * form when the state accepts it.
     */
    public static function templateFor(string $state): ?string
    {
        if (in_array($state, self::EXCLUDED_STATES, true)) {
            return null;
        }

        $template = config("resale_cert.states.{$state}.template");

        if (filled($template)) {
            return $template;
        }

        $rule = ResaleStateRule::where('state_code', $state)->first();

        return $rule?->accepts_mtc ? 'mtc.pdf' : null;
    }
}
