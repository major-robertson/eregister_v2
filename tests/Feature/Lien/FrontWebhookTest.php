<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Domains\Lien\Models\LienProject;
use App\Jobs\ProcessFrontWebhookEvent;
use App\Mail\FilingActionReminder;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

/*
| EREG-132: a Front rule posts every inbound message in the contact@ inbox to
| /webhooks/front. A customer's email flags the filings waiting on them and
| pauses their reminders; it never changes a status, and nothing from the
| message body is kept.
*/

if (! function_exists('frontEvent')) {
    /** An inbound_received webhook body carrying the message. */
    function frontEvent(array $recipients, array $message = [], array $conversation = []): array
    {
        return [
            'type' => 'inbound_received',
            'authorization' => ['id' => 'cmp_123'],
            'payload' => [
                'id' => 'evt_1',
                'type' => 'inbound',
                'conversation' => array_merge([
                    'id' => 'cnv_abc123',
                    'subject' => 'Re: We need information from you',
                ], $conversation),
                'target' => [
                    '_meta' => ['type' => 'message'],
                    'data' => array_merge([
                        'id' => 'msg_1',
                        'is_inbound' => true,
                        'subject' => 'Re: We need information from you',
                        'recipients' => $recipients,
                        'body' => '<p>Here is the contract. Account 4111 1111.</p>',
                        'text' => 'Here is the contract. Account 4111 1111.',
                        'blurb' => 'Here is the contract.',
                    ], $message),
                ],
            ],
        ];
    }
}

if (! function_exists('frontRecipients')) {
    /** From $from to our inbox, with $cc copied in order. */
    function frontRecipients(string $from, array $cc = []): array
    {
        return array_merge(
            [['handle' => $from, 'role' => 'from'], ['handle' => 'contact@eregister.com', 'role' => 'to']],
            array_map(fn (string $address) => ['handle' => $address, 'role' => 'cc'], $cc),
        );
    }
}

if (! function_exists('frontPost')) {
    /** POST a raw body to the webhook with the given headers. */
    function frontPost(string $body, array $headers = []): TestResponse
    {
        $server = ['CONTENT_TYPE' => 'application/json'];

        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return test()->call('POST', route('webhooks.front'), [], [], [], $server, $body);
    }
}

if (! function_exists('frontSigned')) {
    /** Front's signature headers for a body: base64(HMAC-SHA256("{timestamp}:{body}")). */
    function frontSigned(string $body, string $secret = 'front-signing-secret', string $timestamp = '1792500000000'): array
    {
        return [
            'x-front-request-timestamp' => $timestamp,
            'x-front-signature' => base64_encode(hash_hmac('sha256', $timestamp.':'.$body, $secret, true)),
        ];
    }
}

if (! function_exists('frontFiling')) {
    /**
     * A paid filing for the project, created by $user. A waiting status is
     * reached through transitionTo, so its reminder sequence starts.
     */
    function frontFiling(LienProject $project, User $user, FilingStatus $status): LienFiling
    {
        $filing = LienFiling::factory()->forProject($project)->create([
            'status' => $status->isWaitingOnCustomer() ? FilingStatus::Paid : $status,
            'paid_at' => now(),
            'created_by_user_id' => $user->id,
        ]);

        if ($status->isWaitingOnCustomer()) {
            $filing->transitionTo($status);
        }

        return $filing->fresh();
    }
}

if (! function_exists('frontReminder')) {
    function frontReminder(LienFiling $filing): ?EmailSequence
    {
        return EmailSequence::query()
            ->where('sequence_type', 'filing_action_reminder')
            ->where('sequenceable_type', $filing->getMorphClass())
            ->where('sequenceable_id', $filing->id)
            ->first();
    }
}

if (! function_exists('frontCustomer')) {
    /** A customer with their own business and project. */
    function frontCustomer(string $email): array
    {
        $user = User::factory()->create(['email' => $email]);
        $business = Business::factory()->create();
        $business->users()->attach($user, ['role' => 'owner']);

        return [$user, LienProject::factory()->forBusiness($business)->create()];
    }
}

describe('the endpoint', function () {
    beforeEach(function () {
        Queue::fake();
        config(['services.front.webhook_key' => 'front-signing-secret']);

        // The signature is checked everywhere but local and testing.
        $this->app['env'] = 'production';
    });

    it('accepts a signed inbound message and queues it without the message body', function () {
        $body = json_encode(frontEvent(frontRecipients('dana@customer.test')));

        frontPost($body, frontSigned($body))->assertOk();

        Queue::assertPushed(ProcessFrontWebhookEvent::class, function (ProcessFrontWebhookEvent $job) {
            expect(json_encode($job->data))->not->toContain('Here is the contract')
                ->not->toContain('4111');

            return $job->data['payload']['conversation']['id'] === 'cnv_abc123'
                && $job->data['payload']['message']['recipients'][0]['handle'] === 'dana@customer.test';
        });
    });

    it('rejects a bad or missing signature with a 401', function () {
        $body = json_encode(frontEvent(frontRecipients('dana@customer.test')));

        frontPost($body, frontSigned($body, 'wrong-secret'))->assertStatus(401);
        frontPost($body)->assertStatus(401);
        frontPost($body.' ', frontSigned($body))->assertStatus(401);

        Queue::assertNothingPushed();
    });

    it('rejects everything when no signing secret is set', function () {
        config(['services.front.webhook_key' => null]);
        $body = json_encode(frontEvent(frontRecipients('dana@customer.test')));

        frontPost($body, frontSigned($body))->assertStatus(401);

        Queue::assertNothingPushed();
    });

    it('echoes the challenge Front sends when the webhook is enabled', function () {
        $body = '{}';

        frontPost($body, frontSigned($body) + ['x-front-challenge' => 'chl_42'])
            ->assertOk()
            ->assertExactJson(['challenge' => 'chl_42']);

        Queue::assertNothingPushed();
    });

    it('answers 200 to other events and queues nothing', function () {
        $body = json_encode(['type' => 'outbound_sent', 'payload' => ['conversation' => ['id' => 'cnv_abc123']]]);

        frontPost($body, frontSigned($body))->assertOk();

        Queue::assertNothingPushed();
    });

    it('rejects malformed JSON with a 400', function () {
        $body = '{"type": "inbound_received", ';

        frontPost($body, frontSigned($body))->assertStatus(400);

        Queue::assertNothingPushed();
    });

    it('skips the signature check in local and testing', function (string $environment) {
        $this->app['env'] = $environment;
        $body = json_encode(frontEvent(frontRecipients('dana@customer.test')));

        frontPost($body)->assertOk();

        Queue::assertPushed(ProcessFrontWebhookEvent::class);
    })->with(['local', 'testing']);
});

describe('the job', function () {
    beforeEach(function () {
        Mail::fake();
        $this->freezeTime();

        [$this->customer, $this->project] = frontCustomer('dana@customer.test');
    });

    it('flags the sender\'s waiting filings, logs it and pauses their reminders, leaving the status alone', function () {
        $awaitingClient = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);
        $awaitingNotary = frontFiling($this->project, $this->customer, FilingStatus::AwaitingNotary);
        $working = frontFiling($this->project, $this->customer, FilingStatus::InFulfillment);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('Dana@Customer.TEST')));

        foreach ([$awaitingClient, $awaitingNotary] as $filing) {
            $before = $filing->status;
            $filing->refresh();

            expect($filing->status)->toBe($before)
                ->and($filing->customer_emailed_at?->toDateTimeString())->toBe(now()->toDateTimeString())
                ->and($filing->customer_emailed_front_url)->toBe('https://app.frontapp.com/open/cnv_abc123');

            $events = LienFilingEvent::query()->where('filing_id', $filing->id)->where('event_type', 'customer_emailed')->get();

            expect($events)->toHaveCount(1)
                ->and($events->first()->payload_json)->toMatchArray([
                    'conversation_id' => 'cnv_abc123',
                    'front_url' => 'https://app.frontapp.com/open/cnv_abc123',
                    'sender' => 'dana@customer.test',
                ])
                ->and($events->first()->created_by)->toBeNull()
                ->and(json_encode($events->first()->payload_json))->not->toContain('contract');

            $reminder = frontReminder($filing);

            expect($reminder->paused_at)->not->toBeNull()
                ->and($reminder->pause_reason)->toBe('customer_emailed')
                ->and($reminder->suppressed_at)->toBeNull();
        }

        expect($working->fresh()->customer_emailed_at)->toBeNull()
            ->and(LienFilingEvent::query()->where('filing_id', $working->id)->where('event_type', 'customer_emailed')->exists())->toBeFalse();
    });

    it('sends no reminder while it is paused, and others still go', function () {
        $emailed = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

        [$quiet, $quietProject] = frontCustomer('quiet@customer.test');
        $silent = frontFiling($quietProject, $quiet, FilingStatus::AwaitingClient);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test')));

        $this->travel(3)->days();
        $this->artisan('email:process-sequences')->assertSuccessful();

        Mail::assertQueued(FilingActionReminder::class, 1);
        Mail::assertQueued(FilingActionReminder::class, fn (FilingActionReminder $mail) => $mail->sequence->sequenceable_id === $silent->id);

        expect(frontReminder($emailed)->fresh()->next_send_at)->not->toBeNull()
            ->and(frontReminder($emailed)->suppressed_at)->toBeNull();
    });

    it('lets a resumed sequence send again', function () {
        $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);
        frontReminder($filing)->pause('customer_emailed');

        $this->travel(3)->days();
        $this->artisan('email:process-sequences');
        Mail::assertNotQueued(FilingActionReminder::class);

        frontReminder($filing)->resume();
        expect(frontReminder($filing)->paused_at)->toBeNull();

        $this->artisan('email:process-sequences');
        Mail::assertQueued(FilingActionReminder::class, 1);
    });

    it('falls back to the first customer on Cc when the sender is not one', function () {
        $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('assistant@othercorp.test', [
            'contact@eregister.com',
            'nobody@othercorp.test',
            'dana@customer.test',
        ])));

        expect($filing->fresh()->customer_emailed_at)->not->toBeNull();
    });

    it('goes by the sender before the Cc', function () {
        $danaFiling = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

        [$sam, $samProject] = frontCustomer('sam@builder.test');
        $samFiling = frontFiling($samProject, $sam, FilingStatus::AwaitingEsign);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('sam@builder.test', ['dana@customer.test'])));

        expect($samFiling->fresh()->customer_emailed_at)->not->toBeNull()
            ->and($danaFiling->fresh()->customer_emailed_at)->toBeNull();
    });

    it('flags only the most recently updated open filing when none waits on the customer', function () {
        $older = frontFiling($this->project, $this->customer, FilingStatus::InFulfillment);
        $newer = frontFiling($this->project, $this->customer, FilingStatus::NeedsReview);
        $finished = frontFiling($this->project, $this->customer, FilingStatus::Complete);
        $draft = LienFiling::factory()->forProject($this->project)->create([
            'status' => FilingStatus::Draft,
            'created_by_user_id' => $this->customer->id,
        ]);

        LienFiling::withoutGlobalScope('business')->whereKey($older->id)->update(['updated_at' => now()->subDays(2)]);
        LienFiling::withoutGlobalScope('business')->whereKey($newer->id)->update(['updated_at' => now()->subHour()]);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test')));

        expect($newer->fresh()->customer_emailed_at)->not->toBeNull()
            ->and($newer->fresh()->status)->toBe(FilingStatus::NeedsReview)
            ->and($older->fresh()->customer_emailed_at)->toBeNull()
            ->and($finished->fresh()->customer_emailed_at)->toBeNull()
            ->and($draft->fresh()->customer_emailed_at)->toBeNull()
            ->and(EmailSequence::query()->whereNotNull('paused_at')->exists())->toBeFalse();
    });

    it('logs and stops when the customer has no open filing', function () {
        $finished = frontFiling($this->project, $this->customer, FilingStatus::Complete);
        Log::spy();

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test')));

        expect($finished->fresh()->customer_emailed_at)->toBeNull();

        Log::shouldHaveReceived('info')
            ->with('Front webhook - The customer has no open lien filing', ['sender' => 'dana@customer.test', 'conversation_id' => 'cnv_abc123'])
            ->once();
    });

    it('logs and stops when no customer matches the sender or Cc', function () {
        $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);
        Log::spy();

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('stranger@nowhere.test', ['other@nowhere.test'])));

        expect($filing->fresh()->customer_emailed_at)->toBeNull()
            ->and(LienFilingEvent::query()->where('event_type', 'customer_emailed')->exists())->toBeFalse();

        Log::shouldHaveReceived('info')
            ->with('Front webhook - No customer matches the sender or Cc', ['sender' => 'stranger@nowhere.test', 'conversation_id' => 'cnv_abc123'])
            ->once();
    });

    it('skips mail that is not a customer writing to us', function (string $sender, array $message) {
        [$user, $project] = frontCustomer($sender);
        $filing = frontFiling($project, $user, FilingStatus::AwaitingClient);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients($sender), $message));

        expect($filing->fresh()->customer_emailed_at)->toBeNull()
            ->and(frontReminder($filing)->paused_at)->toBeNull();
    })->with([
        'our domain' => ['agent@eregister.com', []],
        'a subdomain of ours' => ['bounces@mail.eregister.com', []],
        'Major Holdings' => ['major@major.holdings', []],
        'SAM Directory' => ['help@sam.directory', []],
        'postmaster' => ['postmaster@customer.test', []],
        'mailer-daemon' => ['MAILER-DAEMON@customer.test', []],
        'no-reply' => ['no-reply@customer.test', []],
        'noreply' => ['noreply@customer.test', []],
        'donotreply' => ['donotreply@customer.test', []],
        'Automatic reply' => ['dana2@customer.test', ['subject' => 'Automatic reply: We need information from you']],
        'Auto-Reply' => ['dana2@customer.test', ['subject' => 'Auto-Reply: Re: your filing']],
        'Autoreply' => ['dana2@customer.test', ['subject' => 'AUTOREPLY - away']],
        'Out of office' => ['dana2@customer.test', ['subject' => 'Out of Office: back Monday']],
        'Out of the office' => ['dana2@customer.test', ['subject' => 'out of the office until 10/27']],
        'Auto-Submitted header' => ['dana2@customer.test', ['metadata' => ['headers' => ['auto-submitted' => 'auto-replied']]]],
        'Auto-Submitted header, listed' => ['dana2@customer.test', ['headers' => [['name' => 'Auto-Submitted', 'value' => 'auto-generated']]]],
    ]);

    it('skips the conversation subject when it is an auto-reply and the message has none', function () {
        $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test'), ['subject' => null], ['subject' => 'Automatic reply: Lien filing']));

        expect($filing->fresh()->customer_emailed_at)->toBeNull();
    });

    it('counts Auto-Submitted: no as a person writing', function () {
        $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test'), ['metadata' => ['headers' => ['Auto-Submitted' => 'no']]]));

        expect($filing->fresh()->customer_emailed_at)->not->toBeNull();
    });

    it('skips mail from our own people', function () {
        $this->customer->givePermissionTo('lien.view');
        $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test')));

        expect($filing->fresh()->customer_emailed_at)->toBeNull();
    });

    it('skips staff on Cc too', function () {
        $this->customer->givePermissionTo('lien.view');
        $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('assistant@othercorp.test', ['dana@customer.test'])));

        expect($filing->fresh()->customer_emailed_at)->toBeNull();
    });

    it('logs one event per conversation within 10 minutes and moves the timestamp', function () {
        $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);
        $count = fn () => LienFilingEvent::query()->where('filing_id', $filing->id)->where('event_type', 'customer_emailed')->count();

        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test')));

        $this->travel(5)->minutes();
        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test')));

        expect($count())->toBe(1)
            ->and($filing->fresh()->customer_emailed_at->toDateTimeString())->toBe(now()->toDateTimeString());

        // Another conversation is another email.
        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test'), [], ['id' => 'cnv_other9']));

        expect($count())->toBe(2)
            ->and($filing->fresh()->customer_emailed_front_url)->toBe('https://app.frontapp.com/open/cnv_other9');

        $this->travel(11)->minutes();
        ProcessFrontWebhookEvent::dispatchSync(frontEvent(frontRecipients('dana@customer.test')));

        expect($count())->toBe(3)
            ->and(frontReminder($filing)->paused_at)->not->toBeNull();
    });

    describe('reading the message from the Front API', function () {
        beforeEach(function () {
            config([
                'services.front.api_key' => 'front-api-key',
                'services.front.api_base' => 'https://major-holdings.api.frontapp.com',
            ]);
        });

        it('reads the last message when the payload carries no message', function () {
            $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

            Http::fake([
                'major-holdings.api.frontapp.com/messages/msg_9' => Http::response([
                    'id' => 'msg_9',
                    'is_inbound' => true,
                    'subject' => 'Re: We need information from you',
                    'recipients' => frontRecipients('dana@customer.test'),
                    'text' => 'Attached is the signed contract.',
                ]),
            ]);

            $event = frontEvent([], [], ['_links' => ['related' => ['last_message' => 'https://major-holdings.api.frontapp.com/messages/msg_9?referer=conversation']]]);
            unset($event['payload']['target']);

            ProcessFrontWebhookEvent::dispatchSync($event);

            expect($filing->fresh()->customer_emailed_at)->not->toBeNull();

            Http::assertSent(fn (HttpRequest $request) => $request->url() === 'https://major-holdings.api.frontapp.com/messages/msg_9'
                && $request->hasHeader('Authorization', 'Bearer front-api-key'));
        });

        it('reads the message the event points at when it carries no recipients', function () {
            $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

            Http::fake([
                'major-holdings.api.frontapp.com/messages/msg_1' => Http::response([
                    'id' => 'msg_1',
                    'recipients' => frontRecipients('dana@customer.test'),
                ]),
            ]);

            ProcessFrontWebhookEvent::dispatchSync(frontEvent([]));

            expect($filing->fresh()->customer_emailed_at)->not->toBeNull();
        });

        it('takes the newest inbound message of the conversation when nothing points at one', function () {
            $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);

            Http::fake([
                'major-holdings.api.frontapp.com/conversations/cnv_abc123/messages' => Http::response(['_results' => [
                    ['id' => 'msg_3', 'is_inbound' => false, 'created_at' => 1792500300, 'recipients' => frontRecipients('agent@eregister.com')],
                    ['id' => 'msg_2', 'is_inbound' => true, 'created_at' => 1792500200, 'recipients' => frontRecipients('dana@customer.test')],
                    ['id' => 'msg_1', 'is_inbound' => true, 'created_at' => 1792500100, 'recipients' => frontRecipients('stranger@nowhere.test')],
                ]]),
            ]);

            $event = frontEvent([]);
            unset($event['payload']['target']);

            ProcessFrontWebhookEvent::dispatchSync($event);

            expect($filing->fresh()->customer_emailed_at)->not->toBeNull()
                ->and(LienFilingEvent::query()->where('event_type', 'customer_emailed')->value('payload_json')['sender'])->toBe('dana@customer.test');
        });

        it('logs and stops without an API key', function () {
            config(['services.front.api_key' => null]);
            $filing = frontFiling($this->project, $this->customer, FilingStatus::AwaitingClient);
            Http::fake();
            Log::spy();

            $event = frontEvent([]);
            unset($event['payload']['target']);

            ProcessFrontWebhookEvent::dispatchSync($event);

            expect($filing->fresh()->customer_emailed_at)->toBeNull();

            Http::assertNothingSent();
            Log::shouldHaveReceived('warning')
                ->with('Front webhook - The event has no sender and FRONT_API_KEY is not set', ['conversation_id' => 'cnv_abc123'])
                ->once();
        });

        it('throws on an API error so the queue tries again', function () {
            Http::fake(['major-holdings.api.frontapp.com/*' => Http::response(['error' => 'down'], 502)]);

            expect(fn () => ProcessFrontWebhookEvent::dispatchSync(frontEvent([])))
                ->toThrow(RuntimeException::class, 'Front API returned 502 for /messages/msg_1');
        });
    });
});
