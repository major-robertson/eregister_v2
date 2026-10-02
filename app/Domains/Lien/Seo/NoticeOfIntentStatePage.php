<?php

namespace App\Domains\Lien\Seo;

use App\Domains\Lien\Documents\LienDocumentPayload;
use App\Domains\Lien\Documents\LienDocumentRegistry;

/**
 * View model for "/liens/notice-of-intent-to-lien/{state}": whether the state
 * requires a notice of intent before a lien, who sends it to whom and how,
 * what it says, the lien deadline it sits in front of, a free blank of the
 * notice the filing product prepares and the paid service.
 *
 * The requirement wording and cites come from
 * database/data/lien_variants/notice_of_intent.php, never from the
 * lien_deadline_rules noi rows: four of those count back from filing where
 * the statute counts forward (EREG-91).
 */
final class NoticeOfIntentStatePage extends LienVariantStatePage
{
    public const KIND = 'noi';

    public const DATA_FILE = 'notice_of_intent';

    public const ROUTE = 'liens.notice-of-intent-to-lien.state';

    public function title(): string
    {
        return $this->blankTitle !== null
            ? "{$this->name} Notice of Intent to Lien: Rules & Free Form"
            : "{$this->name} Notice of Intent to Lien: Rules & Deadlines";
    }

    public function metaDescription(): string
    {
        $price = '$'.$this->selfServePrice();

        return self::fit(
            "{$this->name} notice of intent to lien: {$this->entry['meta']}.",
            ...array_filter([
                $this->blankTitle !== null ? "Free blank PDF, or we send it for you from {$price}." : null,
                "We prepare and send it from {$price}.",
            ]),
        );
    }

    /* ---------------------------------------------------------- requirement */

    /** 'required' | 'required_some' | 'optional' | 'not_required' */
    public function status(): string
    {
        return $this->entry['status'];
    }

    public function isRequired(): bool
    {
        return in_array($this->status(), ['required', 'required_some'], true);
    }

    public function requirementLabel(): string
    {
        return match ($this->status()) {
            'required' => 'Required',
            'required_some' => 'Required for some claimants or projects',
            'optional' => 'Optional, with a benefit',
            default => 'Not required',
        };
    }

    /** The statute's rule in words, ending with its cite. */
    public function ruleSentence(): string
    {
        return rtrim($this->entry['rule'], '.').' ('.$this->entry['cite'].').';
    }

    /** Why a letter still helps where the statute does not ask for one. */
    public function whySend(): string
    {
        return 'A notice of intent is a final written demand. It names the amount, the property and the date you will file. '
            .'An owner who wants to keep a lien off the title has a clear reason to pay first, and you keep a dated record of the demand.';
    }

    /* ---------------------------------------------------- who, whom and how */

    public function who(): string
    {
        return $this->entry['who']
            ?? 'No one has to. Any claimant with lien rights can send one as a last demand before filing.';
    }

    public function to(): string
    {
        if ($this->entry['to'] !== null) {
            return $this->entry['to'];
        }

        $roles = $this->document['service']['recipients'] ?? ['owner'];
        $labels = array_values(array_unique(array_map(fn (string $role) => match ($role) {
            'owner' => 'the property owner',
            'gc' => 'the general contractor',
            'lender' => 'the construction lender',
            'customer' => 'the party you contracted with',
            default => str_replace('_', ' ', $role),
        }, $roles)));

        return 'No law says. Our letter goes to '.self::joinList($labels).'.';
    }

    public function how(): string
    {
        if ($this->entry['how'] !== null) {
            return $this->entry['how'];
        }

        $method = LienDocumentPayload::deliveryLabel($this->document['service']['method'] ?? 'certified_mail') ?? 'certified mail';

        return "Send it by {$method}, so you can prove when it arrived.";
    }

    /** Days the letter gives the recipient to pay before the lien is filed. */
    public function demandDays(): int
    {
        return (int) ($this->document['sections']['demand_days'] ?? 10);
    }

    /**
     * What the notice must say: the statute's list where it has one, else
     * the elements of the letter the filing product prepares.
     *
     * @return array{items: array<int, string>, cite: string|null, statutory: bool}
     */
    public function contents(): array
    {
        if (! empty($this->entry['contents'])) {
            return ['items' => $this->entry['contents'], 'cite' => $this->entry['contents_cite'], 'statutory' => true];
        }

        $lienTitle = $this->document !== null
            ? LienDocumentRegistry::for($this->code)['kinds']['mechanics_lien']['title']
            : 'lien';

        return [
            'items' => [
                'Your name and the party you contracted with',
                'The property, with its address and county',
                'The labor or materials you furnished, and when',
                'The amount unpaid',
                "A demand to pay within {$this->demandDays()} days, or you will file your ".mb_strtolower($lienTitle),
                'Your signature and the date',
            ],
            'cite' => null,
            'statutory' => false,
        ];
    }

    /** @return array<int, string> */
    public function notes(): array
    {
        return $this->entry['notes'] ?? [];
    }

    /* ---------------------------------------------------------------- facts */

    public function keyFacts(): array
    {
        $facts = [
            ['label' => 'Notice of intent', 'value' => $this->requirementLabel(), 'detail' => $this->entry['cite']],
            ['label' => 'When to send it', 'value' => $this->entry['timing']],
            ['label' => 'Lien deadline', 'value' => ucfirst((string) ($this->lien->headlineLienDeadline() ?? 'See the state lien page'))],
            ['label' => 'Where the lien is filed', 'value' => $this->lien->filingLocationLabel()],
            ['label' => 'Free blank form', 'value' => $this->blankTitle !== null ? 'Yes, PDF' : 'Not yet for '.$this->name, 'detail' => $this->blankTitle],
        ];

        return array_map(fn (array $fact) => array_filter($fact, fn ($v) => $v !== null), $facts);
    }

    public function faq(): array
    {
        $price = $this->selfServePrice();
        $deadline = $this->lienDeadlineSentence();
        $contents = $this->contents();

        $when = $this->isRequired() || $this->status() === 'optional'
            ? ucfirst(rtrim($this->entry['timing'], '.')).'. '.($deadline ?? '')
            : "No law sets a time. Send it early enough that the {$this->demandDays()}-day payment window ends before you must file. ".($deadline ?? '');

        return [
            [
                'q' => "Is a notice of intent to lien required in {$this->name}?",
                'a' => $this->ruleSentence(),
            ],
            [
                'q' => "When do I send a {$this->name} notice of intent to lien?",
                'a' => trim($when),
            ],
            [
                'q' => 'Who gets the notice, and how is it sent?',
                'a' => $this->to().' '.$this->how(),
            ],
            [
                'q' => "What does a {$this->name} notice of intent to lien say?",
                'a' => ($contents['statutory']
                    ? "{$this->name} law requires it to state: "
                    : 'Our letter states: ')
                    .mb_strtolower(implode('; ', $contents['items'])).'.'
                    .($contents['cite'] ? " Source: {$contents['cite']}." : ''),
            ],
            [
                'q' => "Is there a free {$this->name} notice of intent to lien form?",
                'a' => $this->blankTitle !== null
                    ? "Yes. Download the blank {$this->blankTitle} on this page. It is the same notice our service prepares, with every field left blank. Or we prepare and send it for you from \${$price}."
                    : "Not yet for {$this->name}. We prepare and send the notice for you from \${$price}.",
            ],
        ];
    }

    /** @param array<int, string> $items */
    private static function joinList(array $items): string
    {
        if (count($items) <= 1) {
            return $items[0] ?? '';
        }
        $last = array_pop($items);

        return implode(', ', $items).(count($items) > 1 ? ',' : '').' and '.$last;
    }
}
