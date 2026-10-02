<?php

namespace App\Domains\Lien\Seo;

/**
 * View model for "/liens/lien-release/{state}": what the state calls a lien
 * release, how soon it is due after payment and what not releasing costs,
 * where it is recorded, a free blank of the release the filing product
 * prepares and the paid service.
 *
 * The deadline and penalty come from database/data/lien_variants/lien_release.php
 * only where the research confirms them; otherwise the page says to confirm
 * with counsel.
 */
final class LienReleaseStatePage extends LienVariantStatePage
{
    public const KIND = 'lien_release';

    public const DATA_FILE = 'lien_release';

    public const ROUTE = 'liens.lien-release.state';

    public const UNCONFIRMED = 'Not confirmed in our research. Confirm with counsel.';

    public function title(): string
    {
        return $this->blankTitle !== null
            ? "{$this->name} Mechanics Lien Release: Rules & Free Form"
            : "{$this->name} Mechanics Lien Release: Rules & Where to File";
    }

    public function metaDescription(): string
    {
        $price = '$'.$this->selfServePrice();
        $lead = "How to release a paid {$this->name} mechanics lien: the ".mb_strtolower($this->documentName())
            .($this->entry['deadline'] !== null ? ', when it is due' : '')
            .($this->officePhrase() !== null ? ' and where it is recorded.' : '.');

        return self::fit(
            $lead,
            ...array_filter([
                $this->blankTitle !== null ? "Free blank PDF, or we prepare it from {$price}." : null,
                "We prepare it from {$price}.",
            ]),
        );
    }

    /** What the state calls the release, in sentence case. */
    public function documentName(): string
    {
        $title = $this->document['title'] ?? null;

        return $this->entry['name'] ?? ($title !== null ? ucfirst(mb_strtolower($title)) : 'Release of lien');
    }

    /** Whether the name comes from the statute research rather than the generic title. */
    public function hasStatutoryName(): bool
    {
        return $this->entry['name'] !== null;
    }

    /** The section that governs the release, from the research or the state's document file. */
    public function cite(): ?string
    {
        return $this->entry['cite'] ?? $this->document['statute'] ?? null;
    }

    public function deadline(): ?string
    {
        return $this->entry['deadline'];
    }

    public function deadlineSentence(): string
    {
        if ($this->entry['deadline'] === null) {
            return "We could not confirm a statutory deadline to release a paid {$this->name} lien. Release it as soon as you are paid, and confirm with counsel.";
        }

        return trim($this->entry['deadline'].'.'
            .($this->entry['deadline_detail'] ? ' '.$this->entry['deadline_detail'] : '')
            .($this->entry['cite'] ? ' Source: '.$this->entry['cite'].'.' : ''));
    }

    public function penaltySentence(): string
    {
        return $this->entry['penalty']
            ?? "We could not confirm a statutory penalty for not releasing a paid {$this->name} lien. Confirm with counsel.";
    }

    /** The office the release is recorded with, mid-sentence, or null when no source confirms it. */
    public function officePhrase(): ?string
    {
        $office = $this->entry['office'];

        if ($office === false) {
            return null;
        }

        return $office !== null ? lcfirst($office) : $this->lien->filingLocationPhrase();
    }

    /** Notarization of the release document, from the state's document file. */
    public function notarySummary(): ?array
    {
        $execution = $this->document['execution'] ?? null;
        if ($execution === null) {
            return null;
        }

        return match (true) {
            (bool) ($execution['notary'] ?? false) => ['value' => 'Required', 'detail' => 'Signed and acknowledged before a notary'],
            ($execution['verification'] ?? null) === 'verified' => ['value' => 'Not required', 'detail' => 'Verified by the claimant'],
            default => ['value' => 'Not required', 'detail' => null],
        };
    }

    /** @return array<int, string> */
    public function notes(): array
    {
        return $this->entry['notes'] ?? [];
    }

    /* ---------------------------------------------------------------- facts */

    public function keyFacts(): array
    {
        $office = $this->officePhrase();
        $notary = $this->notarySummary();

        $facts = [
            ['label' => 'Document', 'value' => $this->documentName(), 'detail' => $this->cite()],
            ['label' => 'When to release', 'value' => $this->entry['deadline'] ?? self::UNCONFIRMED, 'detail' => $this->entry['deadline_detail']],
            ['label' => 'If you do not', 'value' => $this->entry['penalty'] ?? self::UNCONFIRMED],
            ['label' => 'Where it is recorded', 'value' => $office !== null ? ucfirst($office) : self::UNCONFIRMED],
        ];

        if ($notary !== null) {
            $facts[] = ['label' => 'Notary', 'value' => $notary['value'], 'detail' => $notary['detail']];
        }

        $facts[] = ['label' => 'Free blank form', 'value' => $this->blankTitle !== null ? 'Yes, PDF' : 'Not yet for '.$this->name, 'detail' => $this->blankTitle];

        return array_map(fn (array $fact) => array_filter($fact, fn ($v) => $v !== null), $facts);
    }

    public function faq(): array
    {
        $price = $this->selfServePrice();
        $office = $this->officePhrase();

        return [
            [
                'q' => "What is a lien release called in {$this->name}?",
                'a' => $this->hasStatutoryName()
                    ? "{$this->name} law calls it a ".mb_strtolower($this->entry['name']).($this->cite() ? " ({$this->cite()})" : '').'.'
                    : "We have not confirmed a statutory name for it in {$this->name}. A release of lien is the usual title. Confirm the form with counsel.",
            ],
            [
                'q' => "How soon must I release a {$this->name} lien after I am paid?",
                'a' => $this->deadlineSentence(),
            ],
            [
                'q' => "What happens if I do not release a paid lien in {$this->name}?",
                'a' => $this->penaltySentence(),
            ],
            [
                'q' => "Where is a {$this->name} lien release recorded?",
                'a' => $office !== null
                    ? "Record it with the {$office}. Bring the lien's recording details so the release can be matched to it."
                    : (implode(' ', $this->notes()) ?: "We could not confirm where a {$this->name} release is recorded. Confirm with counsel."),
            ],
            [
                'q' => "Is there a free {$this->name} lien release form?",
                'a' => $this->blankTitle !== null
                    ? "Yes. Download the blank {$this->blankTitle} on this page. It is the same release our service prepares, with every field left blank. Or we prepare it for you from \${$price}."
                    : "Not yet for {$this->name}. We prepare the release for you from \${$price}.",
            ],
        ];
    }
}
