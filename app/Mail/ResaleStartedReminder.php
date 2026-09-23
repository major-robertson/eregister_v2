<?php

namespace App\Mail;

use App\Models\EmailSequence;
use App\Models\Price;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * "Get your resale certificate": the resale_started sequence, for someone
 * who came for resale certificates and has not subscribed. An hour after
 * it starts, then a day later, then three days after that (ResaleFollowUp).
 */
class ResaleStartedReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailSequence $sequence,
        public int $step
    ) {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        $stateName = $this->stateName();
        $certificate = $stateName !== null ? "{$stateName} resale certificate" : 'resale certificate';

        return new Envelope(subject: match ($this->step) {
            1 => "Get your {$certificate} today",
            2 => 'Did a vendor ask for your resale certificate?',
            default => 'Do you still need '.$this->withArticle($certificate).'?',
        });
    }

    public function content(): Content
    {
        $stateName = $this->stateName();

        return new Content(
            markdown: 'mail.resale-started',
            with: [
                'userName' => $this->sequence->user->first_name ?? 'there',
                'step' => $this->step,
                'certificate' => $this->withArticle($stateName !== null ? "{$stateName} resale certificate" : 'resale certificate'),
                'stateName' => $stateName,
                'generatorPrice' => $this->price(config('resale_cert.price_family'), config('resale_cert.price_key'), 'default', 'subscription', '$297'),
                'permitPrice' => $this->price('tax', 'sales_tax_permit', 'per_state', 'one_time', '$199'),
                'resumeUrl' => $this->sequence->resume_url ?: route('resale-cert.checkout'),
                'registrationUrl' => route('sales-tax.registrations.start'),
                'preferencesUrl' => URL::signedRoute('email.preferences', ['user' => $this->sequence->user_id]),
            ],
        );
    }

    /**
     * The state they searched for (the ads page they landed on), else the
     * state their business is in.
     */
    private function stateName(): ?string
    {
        $path = (string) $this->sequence->user?->signup_landing_path;

        if (preg_match('#^/lp/resale-certificate/([a-z]{2})\b#i', $path, $match) === 1) {
            $name = config('states.'.strtoupper($match[1]));

            if (is_string($name)) {
                return $name;
            }
        }

        $code = strtoupper((string) (($this->sequence->business?->business_address ?? [])['state'] ?? ''));
        $name = $code !== '' ? config("states.{$code}") : null;

        return is_string($name) ? $name : null;
    }

    /** "a Texas resale certificate", "an Illinois resale certificate". */
    private function withArticle(string $phrase): string
    {
        return (preg_match('/^[AEIOU]/i', $phrase) === 1 ? 'an ' : 'a ').$phrase;
    }

    private function price(string $family, string $key, string $variant, string $billing, string $fallback): string
    {
        try {
            return '$'.number_format(Price::resolve($family, $key, $variant, $billing)->amount_cents / 100);
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
