<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Lien\Enums\WaiverKind;

/**
 * Sample-filled copies of a state's real waiver forms, for the marketing
 * pages. Each preview is the generator's own document shell and body rendered
 * with made-up details, so the page shows exactly what the wizard produces
 * and can never drift from it. Nothing is stored and no PDF is made; the HTML
 * goes into a sandboxed iframe, which keeps the document's print styles away
 * from the page around it.
 */
class WaiverFormPreview
{
    /**
     * Added inside the framed document only: page-like margins, and a
     * highlight on every blank the visitor's own details would fill.
     */
    private const FRAME_STYLES = '<style>'
        .'html,body{margin:0;background:#fff;overflow:hidden}'
        .'body{padding:54px 64px}'
        .'.fill,.fill-wide,.waiver-fields td.value,.gw-value{background:#fde68a;box-shadow:0 0 0 2px #fde68a;border-radius:2px}'
        .'</style>';

    public function __construct(private WaiverFormResolver $resolver) {}

    /**
     * One preview per waiver type the state actually uses, in the order the
     * starter lists them. With no state (the all-states ads pages) the house
     * form stands in, with the state line left blank.
     *
     * @return array<string, array{title: string, html: string}> keyed by WaiverKind value
     */
    public function for(?string $state): array
    {
        $code = $state !== null ? strtoupper($state) : $this->houseFormState();
        $previews = [];

        foreach ($this->resolver->availableKinds($code) as $kindValue => $entry) {
            if (! $entry['enabled']) {
                continue;
            }

            $form = $this->resolver->resolve($code, $entry['kind']);

            $previews[$kindValue] = [
                'title' => $form->title,
                'html' => $this->render($form, $entry['kind'], $state === null),
            ];
        }

        return $previews;
    }

    private function render(ResolvedWaiverForm $form, WaiverKind $kind, bool $anyState): string
    {
        $html = view('documents.lien.waivers.shell', [
            'waiver' => $this->samplePayload($form, $kind, $anyState),
            'esign' => null,
        ])->render();

        return str_replace('</head>', self::FRAME_STYLES.'</head>', $html);
    }

    /**
     * The same shape WaiverGenerator::data() builds from a real waiver.
     *
     * @return array<string, mixed>
     */
    private function samplePayload(ResolvedWaiverForm $form, WaiverKind $kind, bool $anyState): array
    {
        $stateName = WaiverStateRegistry::STATE_NAMES[$form->state] ?? $form->state;
        $place = $anyState ? 'Anytown' : 'Anytown, '.$form->state;

        return [
            'form' => [
                'template' => $form->template,
                'title' => $form->title,
                'kind' => $kind->value,
                'state' => $form->state,
                'state_name' => $anyState ? '____________' : $stateName,
                'template_version' => $form->templateVersion,
                'statute' => $form->statute,
                'notarization_required' => $form->notarizationRequired,
                'witness_required' => $form->witnessRequired,
                'deemed_effective_days' => $form->deemedEffectiveDays,
                'extra_clauses' => $form->extraClauses,
            ],
            'date' => now()->eastern()->format('F j, Y'),
            'claimant' => [
                'company' => 'Acme Drywall LLC',
                'name' => 'Jordan Reyes',
                'address_lines' => ['410 Sample Rd', $place],
                'email' => null,
                'phone' => null,
            ],
            'customer' => [
                'company' => 'Summit General Contractors, Inc.',
                'name' => null,
                'address_lines' => ['88 Example Blvd', $place],
                'email' => null,
                'phone' => null,
            ],
            'owner' => [
                'company' => 'Riverside Property Group LP',
                'name' => null,
                'address_lines' => [],
            ],
            'project' => [
                'name' => 'Riverside Office Build-Out',
                'job_number' => '24-117',
                'address_line' => '1200 Example St, '.$place,
                'county' => null,
                'city' => 'Anytown',
                'state' => $anyState ? null : $form->state,
                'zip' => null,
                'legal_description' => $form->requiresLegalDescription ? 'Lot 4, Block 2, Sample Addition' : null,
                'apn' => null,
            ],
            'amount' => '18,450.00',
            'through_date' => now()->eastern()->subMonthNoOverflow()->endOfMonth()->format('F j, Y'),
            'invoice_number' => '1042',
            'check_maker' => 'Summit General Contractors, Inc.',
            'check_number' => '20417',
            'exceptions' => null,
            'signer' => [
                'name' => 'Jordan Reyes',
                'title' => 'Owner',
                'email' => null,
                'company' => 'Acme Drywall LLC',
            ],
        ];
    }

    /**
     * Any state that leaves the wording to the parties: its forms are the
     * house forms every such state gets.
     */
    private function houseFormState(): string
    {
        foreach (array_keys(WaiverStateRegistry::STATE_NAMES) as $code) {
            if ((WaiverStateRegistry::for($code)['compliance_standard'] ?? 'generic') === 'generic') {
                return $code;
            }
        }

        return 'OH';
    }
}
