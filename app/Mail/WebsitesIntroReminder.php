<?php

namespace App\Mail;

use App\Models\WebsiteInvitation;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * The one reminder after WebsitesIntro, 3 days before the date the spot is
 * held through. It is a real follow-up to that email, so it carries "Re:" and
 * the headers that put it in the same thread.
 */
class WebsitesIntroReminder extends BroadcastMailable
{
    public function __construct(public WebsiteInvitation $invitation)
    {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: WebsitesIntro::sender(),
            replyTo: WebsitesIntro::replyAddresses($this->invitation->user),
            subject: 'Re: '.WebsitesIntro::subjectFor($this->invitation->user),
        );
    }

    public function content(): Content
    {
        $user = $this->invitation->user;

        return new Content(
            html: 'mail.websites-intro-reminder-html',
            text: 'mail.websites-intro-reminder',
            with: [
                'firstName' => WebsitesIntro::firstName($user) ?? 'there',
                'deadline' => $this->invitation->deadlineForHumans(),
                'unsubscribeUrl' => $this->unsubscribeUrl($user),
                'postalLine' => $this->postalLine(),
            ],
        );
    }

    protected function threadHeaders(): Headers
    {
        $first = $this->invitation->message_id;

        return new Headers(
            references: [$first],
            text: ['In-Reply-To' => "<{$first}>"],
        );
    }
}
