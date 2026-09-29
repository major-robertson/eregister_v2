<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Lien\Enums\LienPackageDocument;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Carbon;

/**
 * What an admin can download for a filing, plus the rule strip and the
 * pre-flight warnings the Documents card shows. Pure PHP over the resolved
 * form and the render payload, shared by the Livewire page and the ZIP.
 *
 * The warnings encode what actually went wrong in the archive: liens
 * recorded without a legal description, non-Latin look-alike characters and
 * "[1, 2]" citation markers pasted from PDFs, an amount that did not add up,
 * a preliminary notice served after Arizona's 20-day window, proofs with the
 * wrong perjury state. Staff fix the data and regenerate; nothing is ever
 * patched after signing.
 */
final class LienDocumentPackage
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  list<array{document: LienPackageDocument, recipient: int|null, label: string, sublabel: string|null, url: string}>  $items
     * @param  list<string>  $warnings
     */
    private function __construct(
        public readonly LienFiling $filing,
        public readonly ?ResolvedLienDocument $form,
        public readonly ?string $unavailableReason,
        public readonly array $payload,
        public readonly array $items,
        public readonly array $warnings,
    ) {}

    public static function forFiling(LienFiling $filing, ?LienDocumentResolver $resolver = null): self
    {
        try {
            $form = ($resolver ?? app(LienDocumentResolver::class))->resolve($filing);
        } catch (LienDocumentUnavailable $e) {
            return new self($filing, null, $e->getMessage(), [], [], []);
        }

        if (! view()->exists($form->body)) {
            return new self($filing, $form, "The {$form->stateName} {$form->title} template isn't built yet.", [], [], []);
        }

        $payload = LienDocumentPayload::fromFiling($filing, $form);

        $items = [[
            'document' => LienPackageDocument::Main,
            'recipient' => null,
            'label' => $form->title,
            'sublabel' => implode(' · ', array_filter([
                $form->stateName,
                $form->countyName ? "{$form->countyName} County" : null,
                "template v{$form->templateVersion}",
            ])),
            'url' => route('admin.liens.documents.download', [$filing->public_id, LienPackageDocument::Main->value]),
        ]];

        return new self($filing, $form, null, $payload, $items, self::warnings($form, $payload));
    }

    public function isAvailable(): bool
    {
        return $this->unavailableReason === null;
    }

    /**
     * The rule strip: where it files, how it is signed, who gets served.
     *
     * @return list<array{label: string, value: string}>
     */
    public function rules(): array
    {
        if ($this->form === null) {
            return [];
        }

        $form = $this->form;
        $rules = [];

        if ($form->isInstrument()) {
            $office = $form->recording['filing_office'] ?? [];
            $method = match ($office['method'] ?? 'either') {
                'erecord' => 'e-recording'.(! empty($office['vendor']) ? " via {$office['vendor']}" : ''),
                'mail' => 'by mail',
                default => 'e-recording or mail',
            };
            $lines = array_values(array_filter((array) ($office['address_lines'] ?? [])));

            $rules[] = [
                'label' => 'File with',
                'value' => trim(($office['label'] ?? 'County recorder').", {$method}".($lines ? ' ('.implode(', ', $lines).')' : '')),
            ];

            if (! empty($form->recording['fee_note'])) {
                $rules[] = ['label' => 'Fee', 'value' => (string) $form->recording['fee_note']];
            }
        }

        $rules[] = ['label' => 'Signing', 'value' => self::executionLabel($form->execution)];
        $rules[] = ['label' => 'Serve', 'value' => self::serviceLabel($form)];

        if ($form->attachments !== []) {
            $rules[] = ['label' => 'Attach', 'value' => implode(' ', $form->attachments)];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $execution
     */
    public static function executionLabel(array $execution): string
    {
        $notary = (bool) ($execution['notary'] ?? false);
        $verification = (string) ($execution['verification'] ?? 'none');

        $label = match (true) {
            $notary && ($execution['notary_form'] ?? null) === 'acknowledgment' => 'Signed and acknowledged before a notary',
            $notary => 'Sworn to and signed before a notary (jurat)',
            $verification === 'verified' => 'Signed and verified under penalty of perjury; no notary',
            default => 'Signed by the claimant; no notary',
        };

        if (! empty($execution['witness'])) {
            $label .= '; one witness';
        }

        return $label;
    }

    public static function serviceLabel(ResolvedLienDocument $form): string
    {
        $roles = array_map(fn (string $role) => match ($role) {
            'owner' => 'the owner',
            'gc' => 'the general contractor',
            'lender' => 'the construction lender',
            'customer' => 'the hiring party',
            'subcontractor' => 'the subcontractor',
            default => 'the '.str_replace('_', ' ', $role),
        }, $form->recipientRoles());

        $who = count($roles) > 1
            ? implode(', ', array_slice($roles, 0, -1)).' and '.end($roles)
            : ($roles[0] ?? 'the owner');

        $method = LienDocumentPayload::deliveryLabel($form->service['method'] ?? null) ?? 'certified mail';
        $days = $form->service['days_after'] ?? null;

        $when = match (true) {
            $days === null => '',
            (int) $days === 0 => $form->isInstrument() ? ' at recording' : ' at service',
            $form->isInstrument() => " within {$days} days after recording",
            default => " within {$days} days after first furnishing",
        };

        return ucfirst("{$who} by {$method}{$when}.");
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private static function warnings(ResolvedLienDocument $form, array $payload): array
    {
        $warnings = $form->warnings;
        $project = $payload['project'];
        $parties = $payload['parties'];
        $filing = $payload['filing'];

        if ($form->isInstrument() && $project['legal_description'] === null) {
            $warnings[] = 'No legal description on the project; recorders reject instruments without one.';
        }

        if ($project['address']['line1'] === null) {
            $warnings[] = 'The project has no jobsite street address.';
        }

        if (($parties['claimant']['display_name'] ?? null) === null) {
            $warnings[] = 'No claimant name: add the claimant party or the business name.';
        } elseif (($parties['claimant']['address_lines'] ?? []) === []) {
            $warnings[] = 'The claimant has no mailing address on the party or the business.';
        }

        if ($parties['owner'] === null) {
            $warnings[] = 'No owner party on the project.';
        } elseif ($parties['owner']['address_lines'] === []) {
            $warnings[] = 'The owner party has no mailing address.';
        }

        foreach ($form->recipientRoles() as $role) {
            if ($role !== 'owner' && ($parties[$role] ?? null) === null) {
                $label = match ($role) {
                    'gc' => 'general contractor',
                    'lender' => 'construction lender',
                    'customer' => 'hiring party',
                    default => str_replace('_', ' ', $role),
                };
                $warnings[] = "{$form->stateName} serves the {$label}; the project has no {$label} party.";
            }
        }

        if ($payload['signer']['name'] === null) {
            $warnings[] = 'No signer: add a responsible person who can sign liens under the business, or set one in Document details.';
        }

        if ($form->kind !== 'prelim_notice' && $filing['amount_cents'] === null) {
            $warnings[] = 'No amount claimed on the filing and no balance due on the project.';
        }

        if ($form->kind === 'prelim_notice' && $filing['amount_cents'] === null && $payload['details']['estimated_price_cents'] === null) {
            $warnings[] = 'No estimated price for the notice: set it in Document details or on the project.';
        }

        if (($form->sections['amount'] ?? null) === 'breakdown') {
            $amounts = $project['amounts'];
            $claimed = $filing['amount_cents'];

            if ($claimed !== null && $amounts['contract']['cents'] !== null) {
                $computed = $amounts['contract']['cents']
                    + ($amounts['change_orders']['cents'] ?? 0)
                    - ($amounts['credits']['cents'] ?? 0)
                    - ($amounts['payments']['cents'] ?? 0)
                    - ($amounts['uncompleted']['cents'] ?? 0);

                if ($computed !== $claimed) {
                    $warnings[] = sprintf(
                        'The amount claimed ($%s) does not equal contract + change orders − credits − payments − uncompleted work ($%s).',
                        number_format($claimed / 100, 2),
                        number_format($computed / 100, 2),
                    );
                }
            }
        }

        if ($form->kind === 'mechanics_lien' && $project['dates']['last_furnish'] === null && ($form->sections['last_furnish'] ?? true)) {
            $warnings[] = 'No last furnishing date on the project.';
        }

        if ($form->kind === 'mechanics_lien'
            && ($form->sections['prior_notice'] ?? false)
            && $project['in_privity'] === false
            && $project['dates']['prelim_sent'] === null
            && $payload['details']['notice_served_at'] === null) {
            $warnings[] = 'The claimant did not contract with the owner and no preliminary notice service date is recorded (project or Document details).';
        }

        if ($form->kind === 'lien_release' && $payload['original_lien']['recording_reference'] === null && $payload['original_lien']['recorded_at'] === null) {
            $warnings[] = 'No recording reference or date for the lien being released: set the original lien in Document details.';
        }

        if ($form->kind === 'prelim_notice' && $form->state === 'AZ' && $project['dates']['first_furnish'] !== null) {
            $first = Carbon::parse($project['dates']['first_furnish']);

            if ($first->addDays(20)->lt(now()->eastern()->startOfDay())) {
                $warnings[] = 'Arizona: more than 20 days have passed since first furnishing, so this notice only reaches back 20 days (A.R.S. § 33-992.01(E)).';
            }
        }

        if ($payload['preparer']['address_lines'] === []) {
            $warnings[] = 'The preparer / return-to block has no address: fill lien.documents.preparer in config/lien.php.';
        }

        return array_values(array_unique(array_merge(
            $warnings,
            self::textProblems('The legal description', $project['legal_description']),
            self::textProblems('The description of work', $filing['description_of_work']),
        )));
    }

    /**
     * Characters that survive a paste from a PDF but not a recorder's scan:
     * Cyrillic or Greek look-alikes inside Latin words (a recorded Florida
     * lien read "LO 14" because its T was U+0422), and "[1, 2]" citation
     * markers left by an AI summary.
     *
     * @return list<string>
     */
    private static function textProblems(string $label, ?string $text): array
    {
        if ($text === null || $text === '') {
            return [];
        }

        $problems = [];

        if (preg_match('/[\p{Cyrillic}\p{Greek}]/u', $text)) {
            $problems[] = "{$label} contains non-Latin look-alike characters (pasted from a PDF?); retype them.";
        }

        if (preg_match('/\[\d+(?:,\s*\d+)*\]/', $text)) {
            $problems[] = "{$label} contains citation markers like \"[1, 2]\"; remove them.";
        }

        return $problems;
    }
}
