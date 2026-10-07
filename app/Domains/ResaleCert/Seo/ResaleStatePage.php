<?php

namespace App\Domains\ResaleCert\Seo;

use App\Domains\ResaleCert\Models\ResaleStateRule;
use App\Support\Seo\States;
use App\Support\Seo\Text;
use App\Support\Seo\Urls;
use Carbon\Carbon;

/**
 * Plain-English view model for "/resale-certificates/{state}". The rule
 * facts come from ResaleStateRule and the per-state certificate config; the
 * researched content file (ResaleStateContent) supplies the agency, the form,
 * sources and state-specific copy. Where the research has a value for one of
 * the accepted-certificate rules, the page shows the researched value; the
 * rule row (which drives the generator) is left alone.
 *
 * States with no statewide sales tax (NO_SALES_TAX_STATES) have no rule
 * row. Their page is built from the content file alone, against an unsaved
 * rule that carries only the code and name, and answers what a buyer from
 * that state gives suppliers in other states. See noSalesTax().
 */
final class ResaleStatePage
{
    public readonly string $name;

    public readonly string $slug;

    /** "a" or "an", for "an Alabama resale certificate" / "a Texas resale certificate". */
    public readonly string $article;

    /** @var array<string, mixed> */
    public readonly array $config;

    /** @var array<string, mixed>|null */
    public readonly ?array $content;

    private function __construct(
        public readonly string $code,
        public readonly ResaleStateRule $rule,
    ) {
        $this->name = $rule->state_name;
        $this->slug = States::slug($this->name);
        $this->article = Text::article($this->name);
        $this->config = config("resale_cert.states.{$this->code}", []);
        $this->content = ResaleStateContent::for($this->code);
    }

    /** Bump when the shape of this object or the page copy changes: the cached instances are replaced on the next request. */
    public const CACHE_VERSION = 5;

    /**
     * No statewide sales tax and no rule row, but a page all the same: buyers
     * there still search for a resale certificate because suppliers in other
     * states ask for one. Each needs a content file with 'no_sales_tax' => true.
     */
    public const NO_SALES_TAX_STATES = ['DE', 'MT', 'NH', 'OR'];

    public static function cacheKey(string $code): string
    {
        return 'seo.resale-state.v'.self::CACHE_VERSION.'.'.strtoupper($code);
    }

    public static function statesCacheKey(): string
    {
        return 'seo.resale-states.v'.self::CACHE_VERSION;
    }

    /**
     * Every state with a resale certificate rule, plus the no-sales-tax
     * states with a content file, code => name, sorted by name. The sitemap
     * and the hub page both call it.
     *
     * @return array<string, string>
     */
    public static function availableStates(): array
    {
        $states = ResaleStateRule::query()
            ->statesOnly()
            ->orderBy('state_name')
            ->pluck('state_name', 'state_code')
            ->all();

        foreach (self::NO_SALES_TAX_STATES as $code) {
            if (! isset($states[$code]) && self::hasNoSalesTaxContent($code)) {
                $states[$code] = States::name($code);
            }
        }

        asort($states, SORT_STRING);

        return $states;
    }

    public static function isNoSalesTaxState(string $code): bool
    {
        return in_array(strtoupper($code), self::NO_SALES_TAX_STATES, true);
    }

    private static function hasNoSalesTaxContent(string $code): bool
    {
        return self::isNoSalesTaxState($code)
            && States::name($code) !== null
            && (ResaleStateContent::for($code)['no_sales_tax'] ?? false) === true;
    }

    public static function forCode(string $code): ?self
    {
        $code = strtoupper($code);
        $rule = ResaleStateRule::query()->statesOnly()->where('state_code', $code)->first();

        if ($rule) {
            return new self($code, $rule);
        }

        // No rule row: an unsaved rule carrying only the code and name gives
        // the page its name and slug. Nothing reads its certificate fields.
        return self::hasNoSalesTaxContent($code)
            ? new self($code, new ResaleStateRule(['state_code' => $code, 'state_name' => States::name($code)]))
            : null;
    }

    public function url(): string
    {
        return Urls::absolute(route('resale-certificates.state', ['state' => $this->slug], absolute: false));
    }

    public function title(): string
    {
        if ($this->noSalesTax()) {
            $title = "{$this->name} Resale Certificate: No Sales Tax, What Suppliers Need";

            return mb_strlen($title) <= 60 ? $title : "{$this->name} Resale Certificate: No Sales Tax, What to Use";
        }

        $title = "{$this->name} Resale Certificate | Rules, Forms & Expiration";

        return mb_strlen($title) <= 60 ? $title : "{$this->name} Resale Certificate | Rules & Forms";
    }

    /**
     * At most 165 characters, composed rather than cut: clauses are dropped
     * from the least important up until the sentence fits, so a search
     * result never ends mid-clause or in "...".
     */
    public function metaDescription(): string
    {
        if ($this->noSalesTax()) {
            return $this->noSalesTaxDescription();
        }

        $lead = "{$this->name} resale certificate rules: ";
        $cta = '. Generate signed certificates in minutes.';

        // Listed in page order; the key is the drop priority (highest drops first).
        $clauses = [
            0 => $this->formNumber() ?? $this->formClause(),
            2 => $this->acceptsMtc() ? 'MTC uniform certificate accepted' : 'MTC uniform certificate not accepted',
            3 => $this->acceptsOutOfState() ? 'out-of-state permits accepted' : ($this->isAlaska() ? 'ARSSTC or local number required' : 'in-state permit required'),
            1 => $this->expirationLabel() !== null ? lcfirst($this->expirationLabel()) : $this->expirationPhrase(),
        ];

        $compose = fn (array $kept) => $lead.implode(', ', $kept).$cta;

        while (count($clauses) > 1 && mb_strlen($compose($clauses)) > 165) {
            unset($clauses[max(array_keys($clauses))]);
        }

        return $compose($clauses);
    }

    /** "{State} has no sales tax…" plus the state's own form when it publishes one, within 165 characters. */
    private function noSalesTaxDescription(): string
    {
        $lead = "{$this->name} has no sales tax, but suppliers in other states may ask for a resale certificate.";
        $form = $this->formNumber() ?? $this->formTitle();

        foreach (array_filter([
            $form ? " Use {$form} or the supplier's state form." : null,
            ' Here is what to give them and which local taxes apply.',
            ' Here is what to give them.',
        ]) as $tail) {
            if (mb_strlen($lead.$tail) <= 165) {
                return $lead.$tail;
            }
        }

        return $lead;
    }

    /** The meta description's form clause when the form has no number. */
    private function formClause(): string
    {
        if (! $this->content) {
            return $this->hasOfficialForm() ? 'the official state form' : 'the accepted certificate form';
        }

        if (! $this->formPrescribed()) {
            return 'no prescribed form';
        }

        return $this->isStateIssued() ? 'the state-issued certificate' : 'the official state form';
    }

    /** Whether the generator stamps a PDF (the state's, or the MTC/SST form) rather than drawing its own certificate. */
    public function hasOfficialForm(): bool
    {
        return ! empty($this->config['template']);
    }

    /**
     * Whether the generator produces a certificate for this state. False for
     * the states that issue the certificate themselves and have no generator
     * class (FL, LA, MS, DC): their pages point to the state instead.
     */
    public function hasGenerator(): bool
    {
        return ! empty($this->config['class']);
    }

    /** Where a registered buyer gets the state's own certificate (config 'state_issued'), or null. */
    public function stateIssuedGuidance(): ?string
    {
        return $this->config['state_issued']['guidance'] ?? null;
    }

    /** The rule row's expiration, used when the content file has none. */
    public function expirationPhrase(): string
    {
        $months = $this->rule->expiration_months;
        if (! $months) {
            return 'no fixed expiration';
        }

        $type = $this->rule->metadata['expiration_type'] ?? null;
        $span = $months % 12 === 0 ? ($months / 12 === 1 ? '1 year' : ($months / 12).' years') : "{$months} months";

        return $type === 'end_of_year' ? "expires at year end (up to {$span})" : "valid for {$span}";
    }

    // Accepted certificates: the researched value when there is one, else the rule row.

    public function acceptsMtc(): bool
    {
        return $this->researched('mtc') ?? (bool) $this->rule->accepts_mtc;
    }

    public function acceptsSst(): bool
    {
        return $this->researched('sst') ?? (bool) $this->rule->accepts_sst;
    }

    public function acceptsOutOfState(): bool
    {
        return $this->researched('out_of_state_registration') ?? (bool) $this->rule->accepts_out_of_state;
    }

    public function allowsBlanket(): bool
    {
        return $this->researched('blanket') ?? (bool) $this->rule->allows_blanket;
    }

    private function researched(string $key): ?bool
    {
        $value = $this->content['accepts'][$key]['value'] ?? null;

        return is_bool($value) ? $value : null;
    }

    // States with no sales tax.

    /** No statewide sales tax: the page says what to give suppliers in other states instead of selling a certificate. */
    public function noSalesTax(): bool
    {
        return ($this->content['no_sales_tax'] ?? false) === true && ! $this->rule->exists;
    }

    public function noSalesTaxNote(): ?string
    {
        return $this->content['no_sales_tax_note'] ?? null;
    }

    /** @return array<int, string> What to give a supplier in another state, in order. */
    public function supplierItems(): array
    {
        return $this->content['suppliers'] ?? [];
    }

    /** @return array<int, array{name: string, text: string, source_url: string}> */
    public function localTaxes(): array
    {
        return $this->content['local_taxes'] ?? [];
    }

    /** For the "number they will ask for" card. */
    public function registrationNotes(): ?string
    {
        return $this->content['registration']['notes'] ?? null;
    }

    public function registrationVerifyUrl(): ?string
    {
        return $this->content['registration']['verify_url'] ?? null;
    }

    public function formPdfUrl(): ?string
    {
        return $this->content['form']['pdf_url'] ?? null;
    }

    /** @return array<int, array{title: string, url: string}> */
    public function sources(): array
    {
        return $this->content['sources'] ?? [];
    }

    /** @return array<int, array{q: string, a: string}> */
    private function noSalesTaxFaq(): array
    {
        $answers = $this->content['faq'] ?? [];
        $business = "{$this->article} {$this->name} business";

        return array_values(array_filter([
            ['q' => "Do I need a resale certificate in {$this->name}?", 'a' => $answers['need_certificate'] ?? null],
            ['q' => "What do I give an out-of-state supplier as {$business}?", 'a' => $answers['what_to_give'] ?? null],
            ['q' => "Can {$business} use the MTC uniform certificate?", 'a' => $answers['mtc'] ?? null],
            ['q' => "Do I charge sales tax to my customers in {$this->name}?", 'a' => $answers['charge_customers'] ?? null],
        ], fn (array $item) => $item['a'] !== null));
    }

    // Agency, form and registration.

    /** Alaska has no state sales tax: the ARSSTC and the cities and boroughs administer it. */
    public function isAlaska(): bool
    {
        return $this->code === 'AK';
    }

    public function agencyName(): string
    {
        return $this->content['agency']['name'] ?? "{$this->name} tax agency";
    }

    /** For running copy: "the Comptroller", "CDTFA". */
    public function agencyShort(): string
    {
        return $this->content['agency']['short'] ?? "the {$this->name} tax agency";
    }

    public function agencyUrl(): ?string
    {
        return $this->content['agency']['url'] ?? null;
    }

    public function isStateIssued(): bool
    {
        return ($this->content['issuer_model'] ?? null) === 'state_issued';
    }

    /** "Form 01-339", "Type 2 NTTC", or null when the form has no number. */
    public function formNumber(): ?string
    {
        $number = $this->content['form']['number'] ?? null;
        if (! $number) {
            return null;
        }

        return preg_match('/^(?:Form|Type)\b/i', $number) ? $number : "Form {$number}";
    }

    /** The form title without its trailing parenthetical: "General Resale Certificate". */
    public function formTitle(): ?string
    {
        $title = $this->content['form']['title'] ?? null;

        return $title ? preg_replace('/\s*\([^()]*\)\s*$/', '', $title) : null;
    }

    public function formPrescribed(): bool
    {
        return (bool) ($this->content['form']['prescribed'] ?? false);
    }

    /**
     * "Form 01-339 (Texas Sales and Use Tax Resale Certificate)", the state's
     * own wording when it prescribes no form, or the MTC certificate for a
     * state whose generator uses it and that has no form of its own.
     */
    public function formLabel(): string
    {
        $form = $this->content['form'] ?? null;
        if (! $form) {
            return $this->hasOfficialForm() ? "Official {$this->name} form" : 'State-accepted certificate';
        }

        if (! empty($form['label'])) {
            return $form['label'];
        }

        $title = $this->formTitle();
        if ($number = $this->formNumber()) {
            $label = $number.($title ? " ({$title})" : '');

            return $this->formPrescribed() ? $label : "{$label} or any certificate with the required elements";
        }

        if ($this->formPrescribed() && $title) {
            return $title;
        }

        if (($this->config['template'] ?? '') === 'mtc.pdf') {
            return 'MTC Uniform Sales and Use Tax Certificate';
        }

        return 'No prescribed form; any certificate with the required elements';
    }

    /** The buyer's number as the state names it: "Texas taxpayer number". */
    public function registrationNumberName(): ?string
    {
        $name = $this->content['registration']['number_name'] ?? null;

        return $name ? trim(preg_split('/\s*[(;]/', $name)[0]) : null;
    }

    /** "an Alabama sales tax permit"; Alaska has no state permit. */
    public function salesTaxPermitPhrase(): string
    {
        return $this->isAlaska()
            ? 'an ARSSTC or local sales tax registration'
            : "{$this->article} {$this->name} sales tax permit";
    }

    /** "Texas sales tax"; Alaska's sales taxes are local. */
    public function salesTaxLabel(): string
    {
        return $this->isAlaska() ? 'Alaska local sales tax' : "{$this->name} sales tax";
    }

    /** What the buyer writes in the number field, for the "must include" list. */
    public function buyerNumberPhrase(): string
    {
        if ($number = $this->registrationNumberName()) {
            return $this->acceptsOutOfState()
                ? $number.', or a home-state registration number where allowed'
                : $number;
        }

        return $this->acceptsOutOfState()
            ? "sales tax registration number from {$this->name} or their home state"
            : "{$this->name} sales tax permit number";
    }

    public function expirationLabel(): ?string
    {
        return $this->content['expiration']['label'] ?? null;
    }

    public function expirationSummary(): ?string
    {
        return $this->content['expiration']['summary'] ?? null;
    }

    public function researchedOn(): ?string
    {
        $date = $this->content['researched_on'] ?? null;

        return $date ? Carbon::parse($date)->format('F j, Y') : null;
    }

    /** One line under the hero heading naming the form and the agency; null without researched content. */
    public function heroLine(): ?string
    {
        if (! $this->content) {
            return null;
        }

        return 'Accepted form: '.$this->formLabel().'. Agency: '.$this->agencyName().'.';
    }

    /** @return array<int, string> */
    public function notesParagraphs(): array
    {
        $notes = trim((string) ($this->content['state_notes'] ?? ''));

        return $notes === '' ? [] : preg_split('/\R\s*\R/', $notes);
    }

    /** @return array<int, array{text: string, source_url: string}> */
    public function moreFacts(): array
    {
        return $this->content['facts'] ?? [];
    }

    /**
     * Outbound links for the "Sources" line: the agency's resale page, the
     * form, and the permit lookup.
     *
     * @return array<int, array{label: string, url: string}>
     */
    public function sourceLinks(): array
    {
        $c = $this->content;
        if (! $c) {
            return [];
        }

        $links = [];
        if ($page = $c['resale_page_url'] ?? $c['agency']['url'] ?? null) {
            $links[] = ['label' => "{$this->name} resale certificate guidance", 'url' => $page];
        }
        if ($pdf = $c['form']['pdf_url'] ?? null) {
            $links[] = [
                'label' => $c['form']['pdf_label'] ?? $this->formNumber() ?? $this->formTitle() ?? 'Certificate form',
                'url' => $pdf,
            ];
        }
        if ($verify = $c['registration']['verify_url'] ?? null) {
            $links[] = ['label' => "Verify a buyer's number", 'url' => $verify];
        }

        return $links;
    }

    /**
     * The generator's template, except that a state PDF counts as a plain
     * certificate where the research found the state has no form of its own.
     */
    private function generatorTemplate(): string
    {
        $template = (string) ($this->config['template'] ?? '');
        $noStateForm = $this->content && ! $this->formPrescribed() && ! $this->formNumber();

        return $noStateForm && ! in_array($template, ['mtc.pdf', 'sst.pdf'], true) ? '' : $template;
    }

    /** What the generator's PDF is, for "download a signed certificate on …". */
    public function generatorFormPhrase(): string
    {
        return match ($this->generatorTemplate()) {
            'mtc.pdf' => 'the MTC uniform form',
            'sst.pdf' => 'the Streamlined Sales Tax form',
            '' => 'a compliant form',
            default => 'the state form',
        };
    }

    private function generatorSentence(): string
    {
        if (! $this->hasGenerator()) {
            return $this->stateIssuedGuidance() ?? 'Our generator does not produce this certificate.';
        }

        return match ($this->generatorTemplate()) {
            'mtc.pdf' => 'Our generator produces the MTC uniform certificate.',
            'sst.pdf' => 'Our generator produces the Streamlined Sales Tax certificate.',
            '' => 'Our generator produces a certificate with every element the state requires.',
            default => "Our generator fills in the {$this->name} form for you.",
        };
    }

    // Page sections.

    /** @return array<int, array{label: string, value: string, detail: string}> */
    public function keyFacts(): array
    {
        $r = $this->rule;
        $c = $this->content;

        $facts = [$this->formFact()];

        if ($c) {
            $facts[] = [
                'label' => 'Issued by',
                'value' => $this->isStateIssued() ? ucfirst($this->agencyShort()) : 'The buyer',
                'detail' => $this->isStateIssued()
                    ? "The {$this->agencyName()} issues it to registered businesses. The buyer gives a copy to each supplier.".($this->isAlaska() ? ' Local businesses get theirs from their city or borough.' : '')
                    : 'The buyer fills in and signs the certificate and gives it to each supplier, who keeps it on file.',
            ];

            if ($number = $this->registrationNumberName()) {
                $format = $c['registration']['format'] ?? null;
                $registration = $c['registration']['name'] ?? null;
                $facts[] = [
                    'label' => 'Registration number',
                    'value' => ucfirst($number),
                    'detail' => trim(($format ? 'Format: '.rtrim($format, '.').'. ' : '').($registration ? "It appears on the buyer's {$registration}." : '')),
                ];
            }
        }

        $facts[] = [
            'label' => 'MTC uniform certificate',
            'value' => $this->acceptsMtc() ? 'Accepted' : 'Not accepted',
            'detail' => $this->acceptsMtc()
                ? 'The Multistate Tax Commission uniform certificate can be used for purchases here.'
                : "Vendors in {$this->name} should be given the state's own certificate.",
        ];
        $facts[] = [
            'label' => 'Streamlined (SST) certificate',
            'value' => $this->acceptsSst() ? 'Accepted' : 'Not accepted',
            'detail' => $this->acceptsSst()
                ? 'Sellers here may accept the Streamlined Sales Tax exemption certificate.'
                : "Sellers in {$this->name} should not take the Streamlined Sales Tax exemption certificate as a resale certificate.",
        ];
        $facts[] = [
            'label' => 'Out-of-state buyers',
            'value' => $this->acceptsOutOfState()
                ? 'Home-state permit accepted'
                : ($this->isAlaska() ? 'ARSSTC or local number required' : "{$this->name} permit required"),
            'detail' => $this->acceptsOutOfState()
                ? 'A reseller registered in another state can generally use that registration number.'
                : 'Buyers usually need '.$this->salesTaxPermitPhrase().' to buy tax-free here.',
        ];
        $facts[] = [
            'label' => 'Blanket certificates',
            'value' => $this->allowsBlanket() ? 'Allowed' : 'Single purchase only',
            'detail' => $this->allowsBlanket()
                ? 'One certificate can cover an ongoing series of purchases from the same vendor.'
                : 'A separate certificate is expected for each purchase.',
        ];
        $facts[] = [
            'label' => 'Expiration',
            'value' => $this->expirationLabel() ?? ucfirst($this->expirationPhrase()),
            'detail' => $this->expirationSummary() ?? ($r->expiration_months
                ? 'Vendors should collect a fresh certificate before the old one lapses.'
                : 'Certificates stay valid until the buyer\'s permit is cancelled or the information changes.'),
        ];

        return $facts;
    }

    /** @return array{label: string, value: string, detail: string} */
    private function formFact(): array
    {
        if (! $this->content) {
            return [
                'label' => 'Certificate form',
                'value' => $this->formLabel(),
                'detail' => $this->hasOfficialForm()
                    ? "We fill in the official {$this->name} form for you."
                    : "{$this->name} does not prescribe a single form; we generate a certificate containing every element the state requires.",
            ];
        }

        $form = $this->content['form'];
        $number = $this->formNumber();
        $title = $this->formTitle();
        $agency = $this->agencyName();

        if (! empty($form['label'])) {
            $detail = "{$form['label']}. Rules set by the {$agency}.";
        } elseif ($number || ($this->formPrescribed() && $title)) {
            $what = $number && $title ? "{$number}, the {$title}," : ($number ?? "the {$title}");
            $detail = match (true) {
                ! $this->formPrescribed() => "The {$agency} publishes ".rtrim($what, ',').', but any certificate with the required elements is accepted.',
                $this->isStateIssued() => "The {$agency} issues ".rtrim($what, ',').' to registered businesses.',
                default => "{$what} is published by the {$agency}.",
            };
        } else {
            $detail = "The {$agency} accepts any certificate with the required elements.";
        }

        return [
            'label' => 'Certificate form',
            'value' => $number ?? ($this->formPrescribed() && $title ? $title : 'No prescribed form'),
            'detail' => ucfirst($detail).' '.$this->generatorSentence(),
        ];
    }

    /** @return array<int, array{q: string, a: string}> */
    public function faq(): array
    {
        if ($this->noSalesTax()) {
            return $this->noSalesTaxFaq();
        }

        $r = $this->rule;
        $c = $this->content;

        $items = [
            [
                'q' => "What form do I use for {$this->article} {$this->name} resale certificate?",
                'a' => $c ? $this->formAnswer() : ($this->hasOfficialForm()
                    ? "{$this->name} publishes its own resale certificate form, and vendors expect to see it. Our generator completes the official form with your business, permit, and purchase details and produces a signed PDF."
                    : "{$this->name} does not require one specific form. Any certificate that includes the buyer's name, address, permit number, a description of the property, and a signed statement that it is purchased for resale is accepted. Our generator produces one with every required element."),
            ],
            [
                'q' => "Does {$this->name} accept the MTC uniform resale certificate?",
                'a' => $this->acceptsMtc()
                    ? "Yes. {$this->name} accepts the Multistate Tax Commission's Uniform Sales and Use Tax Certificate, which is convenient when you buy from vendors in several states.".($this->acceptsSst() ? ' The Streamlined Sales Tax exemption certificate is accepted as well.' : '')
                    : "No. {$this->name} does not accept the MTC uniform certificate, so give {$this->name} vendors the state's own certificate.",
            ],
            [
                'q' => "Can an out-of-state business use a resale certificate in {$this->name}?",
                'a' => $this->outOfStateAnswer(),
            ],
            [
                'q' => "Can I give a vendor one blanket resale certificate in {$this->name}?",
                'a' => $this->allowsBlanket()
                    ? "Yes. A blanket certificate in {$this->name} covers every qualifying purchase from that vendor going forward".($r->default_blanket_text ? ', typically described as "'.$r->default_blanket_text.'"' : '').'.'
                    : "No. {$this->name} expects a certificate for each purchase rather than one standing certificate.",
            ],
            [
                'q' => "How long is {$this->article} {$this->name} resale certificate valid?",
                'a' => $this->expirationSummary() ?? ($r->expiration_months
                    ? ucfirst($this->article)." {$this->name} resale certificate is ".$this->expirationPhrase().'. Vendors are responsible for keeping a current certificate on file, so expect to reissue it on that schedule.'
                    : "{$this->name} does not put a fixed expiration on resale certificates. They remain valid as long as the buyer's permit is active and the information on the certificate is still accurate."),
            ],
        ];

        if ($misuse = $c['misuse_penalty']['summary'] ?? null) {
            $items[] = [
                'q' => "What happens if I misuse {$this->article} {$this->name} resale certificate?",
                'a' => $misuse,
            ];
        }

        if ($goodFaith = $c['good_faith']['summary'] ?? null) {
            $items[] = [
                'q' => "When can {$this->article} {$this->name} seller rely on a resale certificate?",
                'a' => $goodFaith,
            ];
        }

        if ($c['registration']['verify_url'] ?? null) {
            $number = $this->registrationNumberName() ?? 'registration number';
            $items[] = [
                'q' => "How do I verify {$this->article} {$this->name} resale certificate?",
                'a' => "Check the buyer's {$number} with the online lookup from the {$this->agencyName()}, linked under Sources on this page. Keep a record of the check with the certificate.",
            ];
        }

        return $items;
    }

    private function outOfStateAnswer(): string
    {
        $notes = $this->content['accepts']['out_of_state_registration']['notes'] ?? null;

        if ($notes) {
            // Notes that open with their own qualifier ("Only for buyers…", "Limited exception…") stand alone.
            return preg_match('/^(?:Limited|Only|Not)\b/', $notes)
                ? $notes
                : ($this->acceptsOutOfState() ? 'Yes, with conditions. ' : 'Generally not. ').$notes;
        }

        return $this->acceptsOutOfState()
            ? "Yes. {$this->name} accepts a valid sales tax registration number from another state on a resale certificate, so you do not need {$this->salesTaxPermitPhrase()} purely to buy inventory tax-free."
            : "Generally not. {$this->name} expects the buyer to hold {$this->salesTaxPermitPhrase()}, so out-of-state resellers who buy here regularly should register first.";
    }

    private function formAnswer(): string
    {
        $form = $this->content['form'];
        $agency = $this->agencyName();
        $number = $this->formNumber();
        $title = $this->formTitle();
        $generator = match (true) {
            ! $this->hasGenerator() => $this->generatorSentence(),
            $this->generatorTemplate() !== '' => $this->generatorSentence().' You get a signed PDF with your business, permit, and purchase details.',
            default => 'Our generator produces a signed certificate with every required element.',
        };

        if (! empty($form['label'])) {
            return "{$form['label']}. The rules come from the {$agency}. {$generator}";
        }

        if ($number || ($this->formPrescribed() && $title)) {
            $what = $number && $title ? "{$number}, the {$title}" : ($number ?? "the {$title}");

            return match (true) {
                ! $this->formPrescribed() => "The {$agency} publishes {$what}, but {$this->name} does not require it. Any certificate with the required elements is accepted.",
                $this->isStateIssued() => "{$this->name} uses {$what}. The {$agency} issues it to registered businesses.",
                default => "{$this->name} uses {$what}, published by the {$agency}.",
            }.' '.$generator;
        }

        return "{$this->name} does not require one specific form. The {$agency} accepts any certificate that includes the buyer's name, address, permit number, a description of the property, and a signed statement that it is purchased for resale. {$generator}";
    }
}
