<?php

use Illuminate\Support\Facades\Mail;

/**
 * These tests deliberately do NOT use Mail::fake(): the guard lives on the
 * MessageSending event inside the real Mailer, which a fake bypasses. The
 * testing env uses the array transport, so sends are captured in memory.
 */
function sentMessages(): Illuminate\Support\Collection
{
    return Mail::getSymfonyTransport()->messages();
}

function sendTo(array|string $to, array $cc = [], array $bcc = []): void
{
    Mail::raw('Test body', function ($message) use ($to, $cc, $bcc) {
        $message->to($to)->subject('Guard test');

        if ($cc !== []) {
            $message->cc($cc);
        }

        if ($bcc !== []) {
            $message->bcc($bcc);
        }
    });
}

it('cancels a message addressed only to a blocked domain', function () {
    sendTo('tester@test.test');

    expect(sentMessages())->toHaveCount(0);
});

it('delivers normally to a real address', function () {
    sendTo('customer@example.com');

    expect(sentMessages())->toHaveCount(1);
});

it('strips the blocked recipient but still delivers to the real one', function () {
    sendTo(['tester@test.test', 'customer@example.com']);

    $messages = sentMessages();
    expect($messages)->toHaveCount(1);

    $recipients = collect($messages->first()->getOriginalMessage()->getTo())
        ->map(fn ($address) => $address->getAddress())
        ->all();

    expect($recipients)->toBe(['customer@example.com']);
});

it('strips blocked addresses from cc and bcc', function () {
    sendTo('customer@example.com', cc: ['cc@test.test'], bcc: ['bcc@test.test', 'real-bcc@example.com']);

    $messages = sentMessages();
    expect($messages)->toHaveCount(1);

    $message = $messages->first()->getOriginalMessage();
    expect($message->getCc())->toBe([]);
    expect(collect($message->getBcc())->map(fn ($a) => $a->getAddress())->all())
        ->toBe(['real-bcc@example.com']);
});

it('matches the domain case-insensitively', function () {
    sendTo('Tester@TEST.TEST');

    expect(sentMessages())->toHaveCount(0);
});

it('does not block a domain that merely ends with the blocked one', function () {
    // nottest.test must not match test.test -- the check is on the full
    // domain, not a suffix.
    sendTo('someone@nottest.test');

    expect(sentMessages())->toHaveCount(1);
});

it('sends everything when no domains are blocked', function () {
    config()->set('mail.blocked_recipient_domains', []);

    sendTo('tester@test.test');

    expect(sentMessages())->toHaveCount(1);
});

it('blocks additional domains from config', function () {
    config()->set('mail.blocked_recipient_domains', ['test.test', 'staging.invalid']);

    sendTo('someone@staging.invalid');

    expect(sentMessages())->toHaveCount(0);
});
