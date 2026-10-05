<?php

namespace App\Mail;

use App\Models\User;
use App\Models\WebsiteInvitation;
use App\Support\HappyWebsites;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Str;

/**
 * The owner inviting a customer to Happy Websites: a free site, the first
 * month free, and a spot held through a date. Sent once per person by
 * email:send-websites-intro. The wording is the owner's own; plain text so it
 * reads like a note he typed.
 *
 * WebsitesIntroReminder follows 3 days before the date, in the same thread.
 */
class WebsitesIntro extends BroadcastMailable
{
    public function __construct(public WebsiteInvitation $invitation)
    {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: self::sender(),
            replyTo: self::replyAddresses($this->invitation->user),
            subject: self::subjectFor($this->invitation->user),
        );
    }

    public function content(): Content
    {
        $user = $this->invitation->user;

        return new Content(
            text: 'mail.websites-intro',
            with: [
                'firstName' => self::firstName($user) ?? 'there',
                'businessName' => $this->invitation->business?->name ?? 'your business',
                'price' => config('happy_websites.price_one_page'),
                'workUrl' => HappyWebsites::url('/work', 'email'),
                'deadline' => $this->invitation->deadlineForHumans(),
                'unsubscribeUrl' => $this->unsubscribeUrl($user),
                'postalLine' => $this->postalLine(),
            ],
        );
    }

    /**
     * Our own Message-ID (Postmark keeps it with X-PM-KeepID), so the
     * reminder can name it and land in the same thread.
     */
    protected function threadHeaders(): Headers
    {
        return new Headers(
            messageId: $this->invitation->message_id,
            text: ['X-PM-KeepID' => 'true'],
        );
    }

    public static function sender(): Address
    {
        return new Address(config('mail.from.address'), config('happy_websites.intro.from_name'));
    }

    public static function subjectFor(User $user): string
    {
        $firstName = self::firstName($user);

        return $firstName === null
            ? 'Can I introduce you to someone?'
            : "{$firstName}, can I introduce you to someone?";
    }

    public static function firstName(User $user): ?string
    {
        $name = trim((string) $user->first_name);

        return $name === '' ? null : Str::ucfirst($name);
    }

    /**
     * A "yes" goes straight to the Happy Websites inboxes. When Postmark
     * inbound is set up, one more address tells the app the customer
     * answered, so the reminder skips them.
     *
     * @return list<Address>
     */
    public static function replyAddresses(User $user): array
    {
        $addresses = array_map(fn (string $inbox) => new Address($inbox), HappyWebsites::inboxes());

        if ($tracking = HappyWebsites::replyTrackingAddress($user)) {
            $addresses[] = new Address($tracking, config('app.name'));
        }

        return $addresses;
    }
}
