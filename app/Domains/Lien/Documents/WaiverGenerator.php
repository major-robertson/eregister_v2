<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\ResolvedWaiverForm;
use App\Domains\Lien\Waivers\WaiverFormResolver;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

/**
 * Builds a lien waiver PDF from a waiver's details. The waiver may be
 * unpersisted (the wizard's free download path renders straight from form
 * state), so data() reads only the model attributes + loaded project.
 *
 * The claimant on the form is whoever is giving up rights: the business itself
 * (provide direction) or the counterparty vendor (collect direction).
 *
 * Rendering is pinned to the DOMPDF driver so it never depends on Chrome.
 */
class WaiverGenerator
{
    public function __construct(
        private readonly WaiverFormResolver $resolver,
    ) {}

    public function render(LienWaiver $waiver): PdfBuilder
    {
        return $this->renderFromSnapshot($this->data($waiver));
    }

    /**
     * Render from a frozen payload (esign locking, signed variants, and
     * re-downloads all render from the snapshot, never live data).
     *
     * @param  array<string, mixed>  $payload
     */
    public function renderFromSnapshot(array $payload): PdfBuilder
    {
        return Pdf::view('documents.lien.waivers.shell', ['waiver' => $payload])
            ->driver('dompdf')
            ->format('letter');
    }

    public function filename(LienWaiver $waiver): string
    {
        $kind = str_replace('_', '-', $waiver->kind->value);
        $state = strtolower($waiver->state);
        $ref = $waiver->public_id ?: Str::lower(Str::random(8));

        return "lien-waiver-{$state}-{$kind}-{$ref}.pdf";
    }

    /**
     * Assemble the template payload. Every field is null-guarded so a thin
     * waiver still renders (blank fill-in lines are legally normal on these
     * forms: several statutory forms are exchanged with blanks).
     *
     * @return array<string, mixed>
     */
    public function data(LienWaiver $waiver): array
    {
        $form = $this->resolver->resolve($waiver->state, $waiver->kind, $waiver->project?->property_class);
        $project = $waiver->project;
        $claimantParty = $project?->claimantParty();
        $ownerParty = $project?->ownerParty();

        $business = [
            'company' => ($claimantParty?->company_name ?: null) ?? $project?->business?->name,
            'name' => $claimantParty?->name ?: null,
            'address_lines' => $claimantParty?->addressLines() ?? [],
            'email' => $claimantParty?->email ?: null,
            'phone' => $claimantParty?->phone ?: null,
        ];

        $counterparty = [
            'company' => $waiver->counterparty_company ?: null,
            'name' => $waiver->counterparty_name ?: null,
            'address_lines' => $this->contactAddressLines($waiver),
            'email' => $waiver->counterparty_email ?: null,
            'phone' => $waiver->counterparty_phone ?: null,
        ];

        // provide: we waive our own rights, our customer receives the waiver.
        // collect: the vendor waives their rights; we are their customer.
        [$claimant, $customer] = $waiver->direction === WaiverDirection::Collect
            ? [$counterparty, $business]
            : [$business, $counterparty];

        return [
            'form' => [
                'template' => $form->template,
                'title' => $form->title,
                'kind' => $waiver->kind->value,
                'state' => $form->state,
                'state_name' => WaiverStateRegistry::STATE_NAMES[$form->state] ?? $form->state,
                'template_version' => $form->templateVersion,
                'statute' => $form->statute,
                'notarization_required' => $form->notarizationRequired,
                'witness_required' => $form->witnessRequired,
                'deemed_effective_days' => $form->deemedEffectiveDays,
            ],
            'date' => now()->eastern()->format('F j, Y'),
            'claimant' => $claimant,
            'customer' => $customer,
            'owner' => [
                'company' => $ownerParty?->company_name ?: null,
                'name' => $ownerParty?->name ?: null,
                'address_lines' => $ownerParty?->addressLines() ?? [],
            ],
            'project' => [
                'name' => $project?->name,
                'job_number' => $project?->job_number,
                'address_line' => $project?->jobsiteAddressLine(),
                'county' => $project?->jobsite_county,
                'city' => $project?->jobsite_city,
                'state' => $project?->jobsite_state,
                'zip' => $project?->jobsite_zip,
                'legal_description' => $project?->legal_description,
                'apn' => $project?->apn,
            ],
            // Number only: the "$" is fixed text in the form templates.
            'amount' => $waiver->amount_cents !== null ? number_format($waiver->amount_cents / 100, 2) : null,
            'through_date' => $waiver->through_date?->format('F j, Y'),
            'invoice_number' => $waiver->invoice_number ?: null,
            'check_maker' => $waiver->check_maker ?: null,
            'check_number' => $waiver->check_number ?: null,
            'exceptions' => $waiver->exceptions ?: null,
            'signer' => [
                'name' => $waiver->signer_name ?: ($waiver->direction === WaiverDirection::Collect ? $counterparty['name'] : $business['name']),
                'title' => $waiver->signer_title ?: null,
                'email' => $waiver->signer_email ?: null,
                'company' => $claimant['company'],
            ],
        ];
    }

    public function resolveForm(LienWaiver $waiver): ResolvedWaiverForm
    {
        return $this->resolver->resolve($waiver->state, $waiver->kind, $waiver->project?->property_class);
    }

    /**
     * @return list<string>
     */
    private function contactAddressLines(LienWaiver $waiver): array
    {
        $contact = $waiver->contact;

        if ($contact === null) {
            return [];
        }

        $cityState = implode(', ', array_filter([$contact->city, $contact->state]));
        $cityStateZip = trim(implode(' ', array_filter([$cityState, $contact->postal_code])));

        return array_values(array_filter([
            $contact->address_line1,
            $contact->address_line2,
            $cityStateZip,
        ]));
    }
}
