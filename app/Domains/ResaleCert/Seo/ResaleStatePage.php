<?php

namespace App\Domains\ResaleCert\Seo;

use App\Domains\ResaleCert\Models\ResaleStateRule;
use App\Support\Seo\States;

/**
 * Plain-English view model for "/resale-certificates/{state}", built from
 * ResaleStateRule and the per-state certificate config so the marketing copy
 * always matches the certificates the generator actually produces. States
 * with no statewide sales tax have no rule row and therefore no page.
 */
final class ResaleStatePage
{
    public readonly string $name;

    public readonly string $slug;

    /** @var array<string, mixed> */
    public readonly array $config;

    private function __construct(
        public readonly string $code,
        public readonly ResaleStateRule $rule,
    ) {
        $this->name = $rule->state_name;
        $this->slug = States::slug($this->name);
        $this->config = config("resale_cert.states.{$this->code}", []);
    }

    /**
     * Every state with a resale certificate rule, code => name. Cached per
     * request by the query builder being cheap; the sitemap and the hub page
     * both call it.
     *
     * @return array<string, string>
     */
    public static function availableStates(): array
    {
        return ResaleStateRule::query()
            ->statesOnly()
            ->orderBy('state_name')
            ->pluck('state_name', 'state_code')
            ->all();
    }

    public static function forCode(string $code): ?self
    {
        $code = strtoupper($code);
        $rule = ResaleStateRule::query()->statesOnly()->where('state_code', $code)->first();

        return $rule ? new self($code, $rule) : null;
    }

    public function url(): string
    {
        return route('resale-certificates.state', ['state' => $this->slug]);
    }

    public function title(): string
    {
        return "{$this->name} Resale Certificate | Rules, Forms & Expiration";
    }

    public function metaDescription(): string
    {
        $bits = [
            "{$this->name} resale certificate rules: ".($this->hasOfficialForm() ? 'the official state form' : 'the accepted certificate form'),
            $this->rule->accepts_mtc ? 'MTC uniform certificate accepted' : 'MTC uniform certificate not accepted',
            $this->rule->accepts_out_of_state ? 'out-of-state permits accepted' : 'in-state permit required',
            $this->expirationPhrase(),
        ];

        $text = implode(', ', array_filter($bits)).'. Generate signed certificates in minutes.';

        return mb_strlen($text) > 165 ? mb_substr($text, 0, 162).'...' : $text;
    }

    public function hasOfficialForm(): bool
    {
        return ! empty($this->config['template']);
    }

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

    /** @return array<int, array{label: string, value: string, detail: string}> */
    public function keyFacts(): array
    {
        $r = $this->rule;

        return [
            [
                'label' => 'Certificate form',
                'value' => $this->hasOfficialForm() ? "Official {$this->name} form" : 'State-accepted certificate',
                'detail' => $this->hasOfficialForm()
                    ? "We fill the {$this->name} Department of Revenue form field by field."
                    : "{$this->name} does not prescribe a single form; we generate a certificate containing every element the state requires.",
            ],
            [
                'label' => 'MTC uniform certificate',
                'value' => $r->accepts_mtc ? 'Accepted' : 'Not accepted',
                'detail' => $r->accepts_mtc
                    ? 'The Multistate Tax Commission uniform certificate can be used for purchases here.'
                    : "Vendors in {$this->name} should be given the state's own certificate.",
            ],
            [
                'label' => 'Streamlined (SST) certificate',
                'value' => $r->accepts_sst ? 'Accepted' : 'Not accepted',
                'detail' => $r->accepts_sst
                    ? "{$this->name} is a Streamlined Sales Tax state and accepts the SST exemption certificate."
                    : "{$this->name} is not a Streamlined Sales Tax member state.",
            ],
            [
                'label' => 'Out-of-state buyers',
                'value' => $r->accepts_out_of_state ? 'Home-state permit accepted' : "{$this->name} permit required",
                'detail' => $r->accepts_out_of_state
                    ? 'A reseller registered in another state can generally use that registration number.'
                    : "Buyers usually need a {$this->name} sales tax registration to buy tax-free here.",
            ],
            [
                'label' => 'Blanket certificates',
                'value' => $r->allows_blanket ? 'Allowed' : 'Single purchase only',
                'detail' => $r->allows_blanket
                    ? 'One certificate can cover an ongoing series of purchases from the same vendor.'
                    : 'A separate certificate is expected for each purchase.',
            ],
            [
                'label' => 'Expiration',
                'value' => ucfirst($this->expirationPhrase()),
                'detail' => $r->expiration_months
                    ? 'Vendors should collect a fresh certificate before the old one lapses.'
                    : 'Certificates stay valid until the buyer\'s permit is cancelled or the information changes.',
            ],
        ];
    }

    /** @return array<int, array{q: string, a: string}> */
    public function faq(): array
    {
        $r = $this->rule;

        $items = [
            [
                'q' => "What form do I use for a {$this->name} resale certificate?",
                'a' => $this->hasOfficialForm()
                    ? "{$this->name} publishes its own resale certificate form, and vendors expect to see it. Our generator completes the official form with your business, permit, and purchase details and produces a signed PDF."
                    : "{$this->name} does not require one specific form. Any certificate that includes the buyer's name, address, permit number, a description of the property, and a signed statement that it is purchased for resale is accepted. Our generator produces one with every required element.",
            ],
            [
                'q' => "Does {$this->name} accept the MTC uniform resale certificate?",
                'a' => $r->accepts_mtc
                    ? "Yes. {$this->name} accepts the Multistate Tax Commission's Uniform Sales and Use Tax Certificate, which is convenient when you buy from vendors in several states.".($r->accepts_sst ? ' The Streamlined Sales Tax exemption certificate is accepted as well.' : '')
                    : "No. {$this->name} does not accept the MTC uniform certificate, so give {$this->name} vendors the state's own certificate.",
            ],
            [
                'q' => "Can an out-of-state business use a resale certificate in {$this->name}?",
                'a' => $r->accepts_out_of_state
                    ? "Yes. {$this->name} accepts a valid sales tax registration number from another state on a resale certificate, so you do not need a {$this->name} permit purely to buy inventory tax-free."
                    : "Generally not. {$this->name} expects the buyer to hold a {$this->name} sales tax permit, so out-of-state resellers who buy here regularly should register with the state first.",
            ],
            [
                'q' => "Can I give a vendor one blanket resale certificate in {$this->name}?",
                'a' => $r->allows_blanket
                    ? "Yes. A blanket certificate in {$this->name} covers every qualifying purchase from that vendor going forward".($r->default_blanket_text ? ', typically described as "'.$r->default_blanket_text.'"' : '').'.'
                    : "No. {$this->name} expects a certificate for each purchase rather than one standing certificate.",
            ],
            [
                'q' => "How long is a {$this->name} resale certificate valid?",
                'a' => $r->expiration_months
                    ? "A {$this->name} resale certificate is ".$this->expirationPhrase().'. Vendors are responsible for keeping a current certificate on file, so expect to reissue it on that schedule.'
                    : "{$this->name} does not put a fixed expiration on resale certificates. They remain valid as long as the buyer's permit is active and the information on the certificate is still accurate.",
            ],
        ];

        return $items;
    }
}
