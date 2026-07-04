<?php

namespace App\Mail;

use App\Domains\Business\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Daily digest of resale certificates approaching expiration for one
 * business, grouped by urgency (<=30 / <=60 / <=90 days).
 */
class ResaleCertificatesExpiring extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Domains\ResaleCert\Models\ResaleCertificate>  $urgent
     * @param  Collection<int, \App\Domains\ResaleCert\Models\ResaleCertificate>  $warning
     * @param  Collection<int, \App\Domains\ResaleCert\Models\ResaleCertificate>  $notice
     */
    public function __construct(
        public Business $business,
        public Collection $urgent,
        public Collection $warning,
        public Collection $notice,
    ) {}

    public function envelope(): Envelope
    {
        $total = $this->urgent->count() + $this->warning->count() + $this->notice->count();

        return new Envelope(
            subject: $total === 1
                ? 'A resale certificate is expiring soon — eRegister'
                : "{$total} resale certificates are expiring soon — eRegister",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.resale-certificates-expiring',
            with: [
                'businessName' => $this->business->legal_name ?? $this->business->name,
                'urgent' => $this->urgent,
                'warning' => $this->warning,
                'notice' => $this->notice,
                'dashboardUrl' => route('resale-cert.certificates.index', ['statusFilter' => 'expiring']),
            ],
        );
    }
}
