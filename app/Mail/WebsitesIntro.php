<?php

namespace App\Mail;

use App\Domains\Business\Models\Business;
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
 * email:send-websites-intro. The wording is the owner's own. The HTML part is
 * bare paragraphs so it reads like a note he typed; it exists so links can be
 * words instead of long URLs.
 *
 * WebsitesIntroReminder follows 3 days before the date, in the same thread.
 */
class WebsitesIntro extends BroadcastMailable
{
    /** Typed into a name field in place of a name. Compared in lower case, without a closing period. */
    private const PLACEHOLDER_NAMES = [
        'none', 'n/a', 'n.a', 'na', 'no', 'null', 'nil', 'tbd', 'unknown', 'not applicable',
        'no business', 'no name', 'self', 'myself', 'me', 'personal', 'individual', 'homeowner',
        'home owner', 'owner', 'test', 'testing', 'x', 'xx', 'xxx',
    ];

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
            html: 'mail.websites-intro-html',
            text: 'mail.websites-intro',
            with: [
                'firstName' => self::firstName($user) ?? 'there',
                'businessName' => self::businessName($this->invitation->business),
                'price' => config('happy_websites.price_one_page'),
                'workUrl' => HappyWebsites::url('/work', 'email'),
                // What the link reads as: "happywebsites.com/work".
                'workLabel' => Str::after(config('happy_websites.url'), '://').'/work',
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

    /**
     * The first name as the greeting and the subject print it, or null when
     * the account has nothing usable. People type their name in all caps or
     * all lower case; a note from the owner shouldn't repeat that. Mixed case
     * ("DeShawn") and two-letter names in caps ("AJ") stay as typed.
     */
    public static function firstName(User $user): ?string
    {
        $name = self::tidyName((string) $user->first_name);

        if ($name === null) {
            return null;
        }

        $shouted = $name === mb_strtoupper($name) && mb_strlen($name) > 2;

        return $shouted || $name === mb_strtolower($name) ? Str::title(mb_strtolower($name)) : $name;
    }

    /**
     * The business name as the email prints it ("I'd like ... to be one of
     * them"), or "your business" when the invitation has no business or its
     * name is a placeholder. The capitals are the customer's own choice and
     * stay: guessing at "ABC PLUMBING LLC" gets acronyms wrong.
     */
    public static function businessName(?Business $business): string
    {
        return self::tidyName((string) $business?->name) ?? 'your business';
    }

    /**
     * Trims what people leave around a name (spaces, a trailing comma) and
     * drops what they type into a required field when they have no answer
     * ("None", "N/A"). A period stays, for names that end in "Inc.".
     */
    private static function tidyName(string $name): ?string
    {
        $name = trim((string) preg_replace('/\s+/u', ' ', $name), " \t\n\r\0\x0B,;:/\\-");

        if (preg_match('/\pL/u', $name) !== 1) {
            return null;
        }

        return in_array(rtrim(mb_strtolower($name), '.'), self::PLACEHOLDER_NAMES, true) ? null : $name;
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
