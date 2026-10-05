<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Bridge\Postmark\Transport\MessageStreamHeader;
use Symfony\Component\Mime\Email;

/**
 * Promotional mail. It goes out on Postmark's broadcast stream, never the
 * transactional one: Postmark pauses sending when it finds promotional mail
 * there, and that would stop e-sign requests, reminders and receipts.
 *
 * Every such email needs a way to unsubscribe and our postal address. The
 * HTML part ends with mail.partials.broadcast-footer, where "Unsubscribe" is
 * a link (the URL is long); the plain-text part prints unsubscribeUrl() and
 * postalLine() at the bottom.
 *
 * The queue-failure fallback in AppServiceProvider looks for this class: an
 * "inactive recipient" rejection on the broadcast stream usually means the
 * person unsubscribed there, not that their address is dead.
 */
abstract class BroadcastMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct()
    {
        $this->afterCommit = true;
    }

    /**
     * Runs when the mail is built for sending (after the queue has restored
     * it), which is why the stream is attached here and not in the
     * constructor: a closure there could not be queued.
     */
    final public function headers(): Headers
    {
        $this->withSymfonyMessage(function (Email $message): void {
            $stream = config('services.postmark.broadcast_stream');

            if (filled($stream) && ! $message->getHeaders()->has('X-PM-Message-Stream')) {
                $message->getHeaders()->add(new MessageStreamHeader($stream));
            }
        });

        return $this->threadHeaders();
    }

    /** Message-ID and reply headers for emails that belong to one thread. */
    protected function threadHeaders(): Headers
    {
        return new Headers;
    }

    /**
     * Postmark requires its own unsubscribe link on the broadcast stream and
     * swaps this placeholder for it (it would add a second link otherwise).
     * The webhook turns that unsubscribe into our "marketing" opt-out. Any
     * other mailer gets our own preferences page.
     */
    protected function unsubscribeUrl(User $recipient): string
    {
        if (config('mail.default') === 'postmark') {
            return '{{{ pm:unsubscribe }}}';
        }

        return URL::signedRoute('email.preferences', ['user' => $recipient->getKey()]);
    }

    /** "eRegister, 123 Main St, ..." (MAIL_POSTAL_ADDRESS). */
    protected function postalLine(): string
    {
        return config('app.name').', '.config('mail.postal_address');
    }
}
