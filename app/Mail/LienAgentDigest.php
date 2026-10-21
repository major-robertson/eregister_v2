<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * The weekday-morning list of red cards on the lien board, for the lien ops
 * inbox (config lien.notifications.ops_email): filings waiting on us too
 * long. Sent by lien:agent-digest, and only when at least one card is red.
 */
class LienAgentDigest extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToSupport, SerializesModels;

    public string $boardUrl;

    /**
     * @param  list<array{business: string, document: string, column: string, alert: string, url: string}>  $cards
     */
    public function __construct(public array $cards)
    {
        $this->boardUrl = route('admin.liens.board');
    }

    public function envelope(): Envelope
    {
        $count = count($this->cards);

        return new Envelope(
            replyTo: $this->supportReplyTo(),
            subject: "Lien board: {$count} ".Str::plural('filing', $count).' waiting on us',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.lien-agent-digest',
        );
    }
}
