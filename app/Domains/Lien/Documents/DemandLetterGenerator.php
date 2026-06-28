<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

/**
 * Builds the Payment Demand Letter PDF(s) from a filing's application data.
 *
 * A demand letter is sent from the claimant (creditor) to one recipient party.
 * A filing can have several non-claimant parties, so we render one letter each —
 * individually, or all at once into a single multi-page PDF (one page per recipient).
 *
 * Method shape (data / filename / render*) is deliberately small so the lien, NOI,
 * preliminary-notice, and resale-certificate generators that follow can copy it.
 * Rendering is pinned to the DOMPDF driver so it never depends on Chrome.
 */
class DemandLetterGenerator
{
    public function render(LienFiling $filing, LienParty $recipient): PdfBuilder
    {
        return Pdf::view('documents.lien.demand-letter', ['letter' => $this->data($filing, $recipient)])
            ->driver('dompdf')
            ->format('letter');
    }

    /**
     * One combined PDF, one page per recipient.
     *
     * @param  Collection<int, LienParty>  $recipients
     */
    public function renderAll(LienFiling $filing, Collection $recipients): PdfBuilder
    {
        $letters = $recipients->map(fn (LienParty $party) => $this->data($filing, $party))->all();

        return Pdf::view('documents.lien.demand-letter-batch', ['letters' => $letters])
            ->driver('dompdf')
            ->format('letter');
    }

    public function filename(LienFiling $filing, ?LienParty $recipient = null): string
    {
        $date = now()->format('Y-m-d');

        if ($recipient === null) {
            return "demand-letters-{$filing->public_id}-all-{$date}.pdf";
        }

        $who = Str::slug($recipient->displayName()) ?: 'recipient';

        return "demand-letter-{$filing->public_id}-{$who}-{$date}.pdf";
    }

    /**
     * Assemble the template payload for a single recipient. Every field is
     * null-guarded so a thin application still renders.
     *
     * @return array<string, mixed>
     */
    public function data(LienFiling $filing, LienParty $recipient): array
    {
        $project = $filing->project;
        $claimant = $project?->claimantParty();

        return [
            'date' => now()->eastern()->format('F j, Y'),
            'recipient' => [
                'name' => $recipient->name ?: null,
                'company' => $recipient->company_name ?: null,
                'address' => $recipient->addressLine() ?: null,
            ],
            'salutation' => $this->salutation($recipient),
            // Number only — the "$" is fixed text in the letter template.
            'amount' => $this->amount($filing, $project),
            // Furnish/completion are date-only fields; format directly (no tz shift).
            'start_date' => $project?->first_furnish_date?->format('F j, Y'),
            'end_date' => ($project?->last_furnish_date ?? $project?->completion_date)?->format('F j, Y'),
            'work' => $filing->description_of_work,
            'sender' => [
                'name' => $claimant?->name ?: null,
                'company' => ($claimant?->company_name ?: null) ?? $project?->business?->name,
                'phone' => $claimant?->phone ?: null,
                'email' => $claimant?->email ?: null,
            ],
        ];
    }

    private function amount(LienFiling $filing, ?LienProject $project): ?string
    {
        $cents = $filing->amount_claimed_cents ?? $project?->balanceDueCents();

        return $cents !== null ? number_format($cents / 100, 2) : null;
    }

    private function salutation(LienParty $recipient): string
    {
        $name = $recipient->name ?: null;
        $company = $recipient->company_name ?: null;

        return match (true) {
            $name && $company => "Dear {$name} of {$company},",
            (bool) $name => "Dear {$name},",
            (bool) $company => "Dear {$company},",
            default => 'To whom it may concern,',
        };
    }
}
