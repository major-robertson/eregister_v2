<?php

namespace App\Support\Email;

use App\Mail\GuestSignerCode;
use App\Models\EmailLog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

/**
 * Writes an email_logs row for every message the app sends (EREG-154).
 * Laravel fires MessageSent for mailables, notifications and Mail::raw()
 * alike, so nothing is sent without a row. A mailable links its row to a
 * filing or application with EmailLog::about() metadata; without it, the
 * row still names the recipient's account.
 *
 * Never throws: the message is already out, and an exception here would
 * fail a queued mail job and send the email again on retry.
 */
class LogSentEmail
{
    /** Emails whose body is a credential (a sign-in code or reset link): logged without it. */
    private const WITHHELD = [
        GuestSignerCode::class,
        ResetPassword::class,
        VerifyEmail::class,
    ];

    public function handle(MessageSent $event): void
    {
        try {
            $this->record($event);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function record(MessageSent $event): void
    {
        $message = $event->sent->getOriginalMessage();

        if (! $message instanceof Email) {
            return;
        }

        $class = $event->data['__laravel_notification'] ?? $event->data['__laravel_mailable'] ?? null;
        $withheld = in_array($class, self::WITHHELD, true);
        $metadata = self::metadata($message);
        $to = self::addresses($message->getTo());
        $html = self::body($message->getHtmlBody());

        EmailLog::create([
            'mailable' => $class,
            'subject' => Str::limit((string) $message->getSubject(), 497),
            'from_address' => ($message->getFrom()[0] ?? null)?->getAddress(),
            'to' => $to,
            'cc' => self::addresses($message->getCc()) ?: null,
            'bcc' => self::addresses($message->getBcc()) ?: null,
            'user_id' => $to === [] ? null : User::query()->whereIn('email', $to)->value('id'),
            'related_type' => $metadata[EmailLog::META_TYPE] ?? null,
            'related_id' => $metadata[EmailLog::META_ID] ?? null,
            'message_id' => $event->sent->getMessageId(),
            'html_body' => $withheld ? null : self::redact($html),
            'text_body' => $withheld || $html !== null ? null : self::redact(self::body($message->getTextBody())),
            'body_withheld' => $withheld,
            'attachments' => array_values(array_filter(array_map(
                fn ($part) => $part->getFilename(),
                $message->getAttachments(),
            ))) ?: null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function metadata(Email $message): array
    {
        $metadata = [];

        foreach ($message->getHeaders()->all() as $header) {
            if ($header instanceof MetadataHeader) {
                $metadata[$header->getKey()] = $header->getValue();
            }
        }

        return $metadata;
    }

    /**
     * @param  array<Address>  $addresses
     * @return list<string>
     */
    private static function addresses(array $addresses): array
    {
        return array_values(array_map(fn (Address $address) => $address->getAddress(), $addresses));
    }

    /**
     * @param  resource|string|null  $body
     */
    private static function body(mixed $body): ?string
    {
        if (is_resource($body)) {
            rewind($body);
            $body = stream_get_contents($body);
        }

        return is_string($body) && $body !== '' ? $body : null;
    }

    /**
     * Breaks the signature on signed links (signing pages, invitations,
     * unsubscribe links), so the stored copy can't be used to act as the
     * recipient. The link still shows where it pointed.
     */
    private static function redact(?string $body): ?string
    {
        return $body === null
            ? null
            : preg_replace('/((?:[?&]|&amp;)signature=)[^&"\'\s<>)\]]+/i', '$1removed', $body);
    }
}
