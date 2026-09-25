<?php

namespace App\Mail;

use App\Domains\SalesTax\SalesTaxFollowUp;
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
 * "Get your sales tax permit": the sales_tax_started sequence, for someone
 * who signed up through a sales tax door and has not picked a state yet. An
 * hour after sign-up, then a day later, then three days after that
 * (SalesTaxFollowUp).
 */
class SalesTaxStartedReminder extends Mailable implements ShouldQueue
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
        $permit = $stateName !== null ? "{$stateName} sales tax permit" : 'sales tax permit';

        return new Envelope(subject: match ($this->step) {
            1 => "Get your {$permit}",
            2 => $stateName !== null ? "We file your {$stateName} sales tax registration for you" : 'We file your sales tax registration for you',
            default => "Still need your {$permit}?",
        });
    }

    public function content(): Content
    {
        $code = $this->stateCode();
        $stateName = $this->stateName();

        return new Content(
            markdown: 'mail.sales-tax-started',
            with: [
                'userName' => $this->sequence->user->first_name ?? 'there',
                'step' => $this->step,
                'stateName' => $stateName,
                'permit' => $stateName !== null ? "{$stateName} sales tax permit" : 'sales tax permit',
                'agency' => $code !== null ? config("sales_tax_states.{$code}.agency") : null,
                'price' => $this->price(),
                'resumeUrl' => $this->sequence->resume_url ?: route('sales-tax.registrations.start'),
                'preferencesUrl' => URL::signedRoute('email.preferences', ['user' => $this->sequence->user_id]),
            ],
        );
    }

    /**
     * The state they picked on the marketing page (carried in the link),
     * else the state their business is in, when we can register there.
     */
    private function stateCode(): ?string
    {
        parse_str((string) parse_url((string) $this->sequence->resume_url, PHP_URL_QUERY), $query);

        $business = $this->sequence->business ?? $this->sequence->user?->businesses()->first();

        foreach ([$query['state'] ?? null, ($business?->business_address ?? [])['state'] ?? null] as $candidate) {
            if (is_string($candidate) && ($code = SalesTaxFollowUp::registrableState($candidate)) !== null) {
                return $code;
            }
        }

        return null;
    }

    private function stateName(): ?string
    {
        $code = $this->stateCode();
        $name = $code !== null ? config("states.{$code}") : null;

        return is_string($name) ? $name : null;
    }

    private function price(): string
    {
        try {
            return '$'.number_format(Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time')->amount_cents / 100);
        } catch (\Throwable) {
            return '$199';
        }
    }
}
