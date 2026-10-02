<?php

namespace App\Domains\SalesTax\Seo;

use App\Domains\SalesTax\SalesTaxFollowUp;
use App\Models\Price;
use App\Support\Seo\States;
use App\Support\Seo\Text;
use App\Support\Seo\Urls;
use Carbon\Carbon;

/**
 * Plain-English view model for "/sales-tax-registration/{state}", built from
 * the researched content file (SalesTaxStateContent). Every fact on the page
 * comes from that file; the price comes from the catalog at render time.
 *
 * The research sometimes carries notes about the research itself ("not
 * confirmed on an ADOR page", "no paper application found"). clean() drops
 * those clauses before anything reaches the page.
 */
final class SalesTaxStatePage
{
    /** Bump when the shape of this object or the page copy changes: the cached instances are replaced on the next request. */
    public const CACHE_VERSION = 1;

    /** Clauses and parentheticals that talk about the research rather than the state. */
    private const RESEARCH_NOTE = '/\b(?:not (?:separately )?(?:confirmed|re-checked|re-verified|verified)|was found|were found|no [\w\' -]+ (?:found|confirmed)|pages reviewed|read today|secondary (?:sources|sites)|automated reads|resale (?:certificate )?research|research packet|is confirmed here|not stated (?:on|by)|this pass)\b/i';

    public readonly string $name;

    public readonly string $slug;

    /** "a" or "an", for "an Arizona Transaction Privilege Tax License". */
    public readonly string $article;

    /** The name after "in": "in Texas", "in the District of Columbia". */
    public readonly string $inName;

    /** @param array<string, mixed> $content */
    private function __construct(
        public readonly string $code,
        public readonly array $content,
    ) {
        $this->name = $content['name'];
        $this->slug = States::slug($this->name);
        $this->article = Text::article($this->name);
        $this->inName = $code === 'DC' ? 'the District of Columbia' : $this->name;
    }

    public static function cacheKey(string $code): string
    {
        return 'seo.sales-tax-state.v'.self::CACHE_VERSION.'.'.strtoupper($code);
    }

    public static function statesCacheKey(): string
    {
        return 'seo.sales-tax-states.v'.self::CACHE_VERSION;
    }

    /**
     * Every state with a content file, code => name.
     *
     * @return array<string, string>
     */
    public static function availableStates(): array
    {
        return SalesTaxStateContent::all();
    }

    public static function forCode(string $code): ?self
    {
        $code = strtoupper($code);
        $content = SalesTaxStateContent::for($code);

        return $content ? new self($code, $content) : null;
    }

    public function url(): string
    {
        return Urls::absolute(route('sales-tax-registration.state', ['state' => $this->slug], absolute: false));
    }

    public function title(): string
    {
        $title = "{$this->name} Sales Tax Registration | {$this->termTitle()}";

        return mb_strlen($title) <= 60 ? $title : "{$this->name} Sales Tax Permit Registration";
    }

    /**
     * 70 to 165 characters, composed rather than cut: the agency's full name
     * gives way to its short name, then the timing clause goes, then the fee,
     * until the sentence fits.
     */
    public function metaDescription(): string
    {
        // Keyed by drop priority (highest drops first), listed in reading order.
        $clauses = array_filter([
            1 => $this->feeClause(),
            2 => $this->timingClause(),
            0 => 'remote sellers register after '.$this->thresholdPhrase(),
        ]);

        $compose = fn (string $agency, array $kept) => "{$this->termWithState()} from {$agency}: ".implode(', ', $kept).'.';

        while (true) {
            foreach (['the '.$this->agencyName(), $this->agencyShort()] as $agency) {
                if (mb_strlen($description = $compose($agency, $clauses)) <= 165) {
                    return $description;
                }
            }

            if (count($clauses) === 1) {
                return $description;
            }

            unset($clauses[max(array_keys($clauses))]);
        }
    }

    private function feeClause(): ?string
    {
        $cents = $this->content['registration']['fee']['amount_cents'] ?? null;

        return match (true) {
            $cents === null => null,
            $cents === 0 => 'no state fee',
            default => $this->money($cents).' state fee',
        };
    }

    /** "2 to 3 weeks online", only when the researched time is a plain duration. */
    private function timingClause(): ?string
    {
        $online = self::clean($this->content['registration']['timing']['online'] ?? null);
        $duration = '/^(?:(?:about|up to|as early as|typically|may be issued)\s+)?(?:the same day|\d+(?: to \d+)? (?:business )?(?:days?|weeks?))$/i';

        return $online && preg_match($duration, $online) ? lcfirst($online).' online' : null;
    }

    // The registration itself.

    /** What the state calls it, without qualifiers: "Sales and Use Tax Permit". */
    public function termLabel(): string
    {
        if ($label = $this->content['term_label'] ?? null) {
            return $label;
        }

        $term = explode(';', $this->content['registration']['term'])[0];

        return trim(preg_replace('/\s*\([^()]*\)/', '', $term));
    }

    /** The term for the title, without a leading state name: "Sales Tax License". */
    public function termTitle(): string
    {
        $label = $this->termLabel();

        return str_starts_with($label, $this->name.' ') ? mb_substr($label, mb_strlen($this->name) + 1) : $label;
    }

    /** "Texas Sales and Use Tax Permit", "Colorado Sales Tax License". */
    public function termWithState(): string
    {
        $label = $this->termLabel();

        return str_starts_with($label, $this->name.' ') ? $label : "{$this->name} {$label}";
    }

    /** The researched term when it says more than the label (two permits, a qualifier). */
    public function fullTerm(): ?string
    {
        $term = self::clean($this->content['registration']['term']);

        return $term !== $this->termLabel() ? $term : null;
    }

    public function agencyName(): string
    {
        return $this->content['agency']['name'];
    }

    /** For running copy: "the Comptroller", "CDTFA". */
    public function agencyShort(): string
    {
        return $this->content['agency']['short'] ?? 'the '.$this->agencyName();
    }

    public function agencyUrl(): ?string
    {
        return $this->content['agency']['url'] ?? null;
    }

    /** @return array{name: string, url: ?string}|null */
    public function portal(): ?array
    {
        $portal = $this->content['registration']['portal'] ?? null;
        if (! ($portal['name'] ?? null)) {
            return null;
        }

        return ['name' => self::clean($portal['name']), 'url' => $portal['url'] ?? null];
    }

    /** "Form AP-201", or null when the state has no numbered form. */
    public function formNumber(): ?string
    {
        $number = $this->content['registration']['form']['number'] ?? null;
        if (! $number) {
            return null;
        }

        return preg_match('/^Form\b/i', $number) ? $number : "Form {$number}";
    }

    public function formTitle(): ?string
    {
        return self::clean($this->content['registration']['form']['title'] ?? null);
    }

    public function formPdfUrl(): ?string
    {
        return $this->content['registration']['form']['pdf_url'] ?? null;
    }

    public function onlineOnly(): bool
    {
        return ($this->content['registration']['form']['online_only'] ?? null) === true;
    }

    public function requiresLocalRegistration(): bool
    {
        return (bool) ($this->content['local_registration']['required'] ?? false);
    }

    /** "$500,000 in sales", "$100,000 in sales or 200 transactions". */
    public function thresholdPhrase(): string
    {
        $economic = $this->content['nexus']['economic'];
        $phrase = $this->money((int) $economic['revenue_usd'] * 100).' in sales';

        if ($transactions = $economic['transactions'] ?? null) {
            $both = str_contains((string) ($economic['period'] ?? ''), 'both tests');
            $phrase .= ($both ? ' and ' : ' or ').number_format($transactions).' transactions';
        }

        return $phrase;
    }

    /** "previous or current calendar year", without the parenthetical detail. */
    public function thresholdPeriod(): ?string
    {
        $period = self::clean($this->content['nexus']['economic']['period'] ?? null);

        return $period ? trim(explode(';', preg_replace('/\s*\((?:[^()]|\([^()]*\))*\)/', '', $period))[0]) : null;
    }

    public function thresholdEffective(): ?string
    {
        $date = $this->content['nexus']['economic']['effective'] ?? null;

        return $date ? Carbon::parse($date)->format('F j, Y') : null;
    }

    public function stateRate(): ?string
    {
        $rate = $this->content['rates']['state_rate_pct'] ?? null;

        return $rate === null ? null : rtrim(rtrim(number_format((float) $rate, 3, '.', ''), '0'), '.').'%';
    }

    // The service.

    /**
     * Whether our registration flow offers this state. Every state the state
     * picker lists can be ordered: the 18 with a state definition file ask
     * their state-specific questions, the rest use the shared questions.
     * DC is not in the picker, so its page is a guide with a softer call to
     * action.
     */
    public function filesWithUs(): bool
    {
        return SalesTaxFollowUp::registrableState($this->code) !== null;
    }

    /** The per-state registration price from the catalog ("$199"), or null when it is not seeded. */
    public function price(): ?string
    {
        try {
            return $this->money(Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time')->amount_cents);
        } catch (\Throwable) {
            return null;
        }
    }

    public function priceDollars(): ?float
    {
        try {
            return Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time')->amount_cents / 100;
        } catch (\Throwable) {
            return null;
        }
    }

    public function registerUrl(): string
    {
        return route('register', ['product' => 'sales-tax', 'state' => $this->code]);
    }

    // Page sections.

    /** One line under the H1 naming the registration and the agency. */
    public function heroLine(): string
    {
        return 'In '.$this->inName." it is called the {$this->termLabel()}. The {$this->agencyName()} issues it.";
    }

    /** @return array<int, array{label: string, value: string, detail: string}> */
    public function keyFacts(): array
    {
        $c = $this->content;
        $registration = $c['registration'];

        $number = self::clean($registration['number']['name'] ?? null);
        $format = self::clean($registration['number']['format'] ?? null);
        $called = array_filter([
            $this->fullTerm() ? 'Full name: '.$this->fullTerm().'.' : null,
            $number ? 'Number issued: '.$number.($format ? ' ('.rtrim($format, '.').')' : '').'.' : null,
        ]);

        $portal = $this->portal();
        $facts = [
            [
                'label' => 'What it is called',
                'value' => $this->termLabel(),
                'detail' => $called ? implode(' ', $called) : "Issued by the {$this->agencyName()}.",
            ],
            [
                'label' => 'Who issues it',
                'value' => $this->agencyName(),
                'detail' => trim(($portal ? "Apply online through {$portal['name']}." : '')
                    .($this->onlineOnly() ? ' Registration is online only.' : ($this->formNumber() ? " Paper form: {$this->formNumber()}." : ''))),
            ],
            [
                'label' => 'State fee',
                'value' => match (true) {
                    ($registration['fee']['amount_cents'] ?? null) === null => 'No fee published',
                    $registration['fee']['amount_cents'] === 0 => 'No state fee',
                    default => $this->money($registration['fee']['amount_cents']),
                },
                'detail' => self::clean($registration['fee']['summary'] ?? null) ?? "Check with {$this->agencyShort()} before you apply.",
            ],
            [
                'label' => 'Processing time',
                'value' => ucfirst(self::clean($registration['timing']['online'] ?? null) ?? 'No firm estimate'),
                'detail' => $this->timingSummary(),
            ],
            [
                'label' => 'Remote seller threshold',
                'value' => ucfirst($this->thresholdPhrase()),
                'detail' => trim(($this->thresholdPeriod() ? 'Measurement period: '.$this->thresholdPeriod().'.' : '')
                    .($this->thresholdEffective() ? ' In effect since '.$this->thresholdEffective().'.' : '')),
            ],
            [
                'label' => 'Filing frequency',
                'value' => ucfirst(trim(preg_replace('/\s*\([^()]*\)/', '', explode(';', (string) $c['filing']['frequencies'])[0]))),
                'detail' => self::clean($c['filing']['rule'] ?? null) ?? "{$this->agencyShort()} assigns the frequency when you register.",
            ],
            [
                'label' => 'State rate',
                'value' => $this->stateRate() ?? 'See the agency',
                'detail' => ($local = self::clean($c['rates']['local'] ?? null)) ? self::sentence(ucfirst($local)) : '',
            ],
        ];

        if ($this->requiresLocalRegistration()) {
            $facts[] = [
                'label' => 'Local registration',
                'value' => 'Required in some places',
                'detail' => $this->leadSentences(self::clean($c['local_registration']['summary'] ?? null) ?? ''),
            ];
        }

        return $facts;
    }

    private function timingSummary(): string
    {
        return self::clean($this->content['registration']['timing']['summary'] ?? null)
            ?? 'Ask '.$this->agencyShort().' for current processing times.';
    }

    /**
     * "How to register in {State}": label, text and an optional link.
     *
     * @return array<int, array{label: string, text: string, link?: array{label: string, url: string}}> */
    public function howToRegister(): array
    {
        $c = $this->content;
        $registration = $c['registration'];
        $steps = [];

        if ($portal = $this->portal()) {
            $steps[] = [
                'label' => 'Where to apply',
                'text' => "Apply online with the {$this->agencyName()}."
                    .($this->onlineOnly() ? ' There is no paper application.' : ''),
                'link' => $portal['url'] ? ['label' => $portal['name'], 'url' => $portal['url']] : null,
            ];
        }

        if ($number = $this->formNumber()) {
            $title = $this->formTitle();
            $steps[] = [
                'label' => 'Application form',
                'text' => $number.($title ? ", {$title}" : '').'.',
                'link' => $this->formPdfUrl() ? ['label' => "{$number} (PDF)", 'url' => $this->formPdfUrl()] : null,
            ];
        }

        if ($needs = self::clean($c['connected']['prerequisites'] ?? null)) {
            $steps[] = ['label' => 'What you need', 'text' => self::sentence($needs)];
        }

        $covers = array_values(array_filter($c['connected']['covers'] ?? []));
        if (count($covers) > 1) {
            $steps[] = ['label' => 'What the application covers', 'text' => ucfirst(self::listPhrase($covers)).'.'];
        }

        $steps[] = ['label' => 'How long it takes', 'text' => $this->timingSummary()];

        if ($fee = self::clean($registration['fee']['summary'] ?? null)) {
            $steps[] = ['label' => 'What it costs', 'text' => $fee];
        }

        if ($renewal = self::clean($registration['renewal']['summary'] ?? null)) {
            $steps[] = ['label' => 'Renewal', 'text' => $renewal];
        }

        if ($this->requiresLocalRegistration() && ($local = self::clean($c['local_registration']['summary'] ?? null))) {
            $steps[] = ['label' => 'Local registration', 'text' => $local];
        }

        return array_map(fn (array $step) => array_filter($step), $steps);
    }

    /**
     * "After you register": filing, due dates, zero returns, rates and sourcing.
     *
     * @return array<int, array{label: string, text: string}> */
    public function afterRegistering(): array
    {
        $c = $this->content;
        $filing = $c['filing'];
        $items = [];

        $frequency = self::clean($filing['frequencies'] ?? null);
        $rule = self::clean($filing['rule'] ?? null);
        if ($frequency || $rule) {
            $items[] = ['label' => 'How often you file', 'text' => trim(($frequency ? self::sentence(ucfirst($frequency)) : '').' '.($rule ?? ''))];
        }

        if ($due = self::clean($filing['due_day'] ?? null)) {
            $items[] = ['label' => 'Due date', 'text' => self::sentence(ucfirst($due))];
        }

        $zero = $filing['zero_return_required'] ?? null;
        if ($zero !== null) {
            $items[] = [
                'label' => 'Periods with no sales',
                'text' => $zero
                    ? 'You still file a return for every period, even with no sales.'
                    : 'A return is not required for a period with no sales.',
            ];
        }

        if ($prepay = self::clean($filing['prepayments'] ?? null)) {
            $items[] = ['label' => 'Prepayments', 'text' => self::sentence(ucfirst($prepay))];
        }

        $rates = array_filter([
            $this->stateRate() ? "The state rate is {$this->stateRate()}." : null,
            ($local = self::clean($c['rates']['local'] ?? null)) ? self::sentence(ucfirst($local)) : null,
            ($sourcing = self::clean($c['rates']['sourcing'] ?? null)) ? 'Sourcing: '.self::sentence(preg_replace_callback('/^([a-z][\w -]*): (\w)/', fn (array $m) => $m[1].'. '.strtoupper($m[2]), $sourcing)) : null,
        ]);
        if ($rates) {
            $items[] = ['label' => 'Rates and sourcing', 'text' => implode(' ', $rates)];
        }

        if ($late = self::clean($c['penalties']['late_filing']['summary'] ?? null)) {
            $items[] = ['label' => 'Late returns', 'text' => $late];
        }

        return $items;
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
        return array_values(array_filter(
            $this->content['facts'] ?? [],
            fn (array $fact) => ($fact['text'] ?? '') !== '' && preg_match('#^https?://#', (string) ($fact['source_url'] ?? '')) === 1,
        ));
    }

    /**
     * Outbound links for the "Sources" line: the agency, the portal and the
     * form PDF.
     *
     * @return array<int, array{label: string, url: string}>
     */
    public function sourceLinks(): array
    {
        $links = [];
        if ($url = $this->agencyUrl()) {
            $links[] = ['label' => $this->agencyName(), 'url' => $url];
        }
        if (($portal = $this->portal()) && $portal['url']) {
            $links[] = ['label' => $portal['name'], 'url' => $portal['url']];
        }
        if ($pdf = $this->formPdfUrl()) {
            $links[] = ['label' => ($this->formNumber() ?? 'Application form').' (PDF)', 'url' => $pdf];
        }

        return $links;
    }

    public function researchedOn(): ?string
    {
        $date = $this->content['researched_on'] ?? null;

        return $date ? Carbon::parse($date)->format('F j, Y') : null;
    }

    /** @return array<int, array{q: string, a: string}> */
    public function faq(): array
    {
        $c = $this->content;
        $registration = $c['registration'];
        $permit = Text::article($this->termWithState()).' '.$this->termWithState();
        $remote = "Sellers with no physical presence must register after {$this->thresholdPhrase()} in {$this->inName}"
            .($this->thresholdPeriod() ? " (measurement period: {$this->thresholdPeriod()})" : '').'.';

        $items = [
            [
                'q' => "Do I need to register for {$this->name} sales tax?",
                'a' => trim((self::clean($c['nexus']['physical'] ?? null) ?? '').' '.$remote),
            ],
            [
                'q' => "How long does it take to get {$permit}?",
                'a' => $this->withCite($this->timingSummary(), $registration['timing']['cite'] ?? null),
            ],
            [
                'q' => "What does {$permit} cost?",
                'a' => $this->withCite(
                    self::clean($registration['fee']['summary'] ?? null) ?? "Check the current fee with {$this->agencyShort()}.",
                    $registration['fee']['cite'] ?? null,
                ).(($this->filesWithUs() && ($price = $this->price())) ? " Our fee to prepare and file the application is {$price}." : ''),
            ],
            [
                'q' => "When do I file {$this->name} sales tax returns?",
                'a' => $this->withCite($this->filingAnswer(), $c['filing']['cite'] ?? null),
            ],
            [
                'q' => "Do I need to register in {$this->inName} as a remote seller?",
                'a' => $this->withCite(
                    trim($remote.' '.(self::clean($c['nexus']['marketplace'] ?? null) ?? '')),
                    $c['nexus']['economic']['cite'] ?? null,
                ),
            ],
        ];

        if ($noPermit = self::clean($c['penalties']['no_permit']['summary'] ?? null)) {
            $items[] = [
                'q' => "What happens if I sell in {$this->inName} without {$permit}?",
                'a' => $this->withCite($noPermit, $c['penalties']['no_permit']['cite'] ?? null),
            ];
        }

        return $items;
    }

    private function filingAnswer(): string
    {
        $filing = $this->content['filing'];
        $parts = [];

        if ($frequency = self::clean($filing['frequencies'] ?? null)) {
            $parts[] = 'Filing frequency: '.self::sentence($frequency);
        }
        if ($rule = self::clean($filing['rule'] ?? null)) {
            $parts[] = $rule;
        }
        if ($due = self::clean($filing['due_day'] ?? null)) {
            $parts[] = 'Due date: '.self::sentence($due);
        }
        if (($filing['zero_return_required'] ?? null) === true) {
            $parts[] = 'File a return every period, even with no sales.';
        }

        return implode(' ', $parts);
    }

    private function withCite(string $answer, ?string $cite): string
    {
        $cite = self::citeText($cite);

        return $cite ? self::sentence($answer)." Source: {$cite}." : $answer;
    }

    // Text helpers.

    /**
     * Drop the research's notes about itself: parentheticals and clauses that
     * say what was or was not found or confirmed. Returns null when nothing
     * is left.
     */
    public static function clean(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $text = preg_replace_callback(
            '/\s*\((?:[^()]|\([^()]*\))*\)/',
            fn (array $m) => preg_match(self::RESEARCH_NOTE, $m[0]) ? '' : $m[0],
            trim($text),
        );

        $kept = [];
        foreach (preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $text) as $sentence) {
            $clauses = preg_split('/;\s+/', $sentence);
            $remaining = array_values(array_filter($clauses, fn (string $clause) => ! preg_match(self::RESEARCH_NOTE, $clause)));
            if (! $remaining) {
                continue;
            }

            $joined = implode('; ', $remaining);
            if ($remaining[0] !== $clauses[0]) {
                $joined = ucfirst($joined);
            }
            if (preg_match('/[.!?]$/', $sentence) && ! preg_match('/[.!?]$/', $joined)) {
                $joined .= '.';
            }
            $kept[] = $joined;
        }

        $result = trim(implode(' ', $kept));

        return $result === '' ? null : $result;
    }

    /** A cite without its URLs, for reading: "Tex. Tax Code 151.708". */
    private static function citeText(?string $cite): ?string
    {
        if (! $cite) {
            return null;
        }

        $cite = preg_replace('/\s*\(?\bhttps?:\/\/[^\s);,]+\)?/', '', $cite);
        $cite = preg_replace(['/\(\s*\)/', '/\s*([;,])(?:\s*[;,])+/', '/\s+/'], ['', '$1', ' '], $cite);
        $cite = trim($cite, " \t\n;,.");

        return $cite === '' ? null : $cite;
    }

    private static function sentence(string $text): string
    {
        $text = trim($text);

        return preg_match('/[.!?]$/', $text) ? $text : $text.'.';
    }

    /** @param array<int, string> $items */
    private static function listPhrase(array $items): string
    {
        if (count($items) < 3) {
            return implode(' and ', $items);
        }

        return implode(', ', array_slice($items, 0, -1)).' and '.end($items);
    }

    /** The first sentences of a text, at least 60 characters, for a key-fact card. */
    private function leadSentences(string $text): string
    {
        $out = '';
        foreach (preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $text) as $sentence) {
            $out = trim($out.' '.$sentence);
            if (mb_strlen($out) >= 60) {
                break;
            }
        }

        return $out;
    }

    private function money(int $cents): string
    {
        return '$'.number_format($cents / 100, $cents % 100 === 0 ? 0 : 2);
    }
}
