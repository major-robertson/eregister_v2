<?php

namespace App\Jobs;

use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * One inbound email to the contact@ inbox, from the Front rule webhook
 * (EREG-132). Customers answer by email even when the filing page offers a
 * form, so their email flags the filings waiting on them and pauses those
 * reminders (LienFiling::recordCustomerEmail). It never changes a status: the
 * agent reads the email in Front and decides.
 *
 * Who it is: the From address, else the first Cc address, matched to a user
 * (case-insensitive). Mail from us, from staff, from robots and auto-replies
 * is skipped. Which filings: the user's businesses' filings in Awaiting
 * Client, E-Signature or Notary; when there are none, their most recently
 * updated open filing.
 *
 * No message body is stored or logged: the job keeps only the event's
 * addresses, subject and headers (slim()), and logs the sender and the
 * conversation id.
 */
class ProcessFrontWebhookEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * A failed Front API read throws so the queue tries again. Retries are
     * harmless: recordCustomerEmail logs one event per conversation per 10
     * minutes and only moves the timestamp.
     */
    public $tries = 3;

    public $timeout = 120;

    /** Our own mail (replies from us, Major Holdings, SAM Directory), subdomains included. */
    private const OUR_DOMAINS = ['eregister.com', 'major.holdings', 'sam.directory'];

    /** Robots: bounces and no-reply senders. */
    private const AUTOMATED_LOCAL_PARTS = ['postmaster', 'mailer-daemon', 'no-reply', 'noreply', 'donotreply'];

    /** Auto-replies, by how the subject starts (case-insensitive). */
    private const AUTO_REPLY_SUBJECTS = ['automatic reply', 'auto-reply', 'autoreply', 'out of office', 'out of the office'];

    /** Front ids (cnv_..., msg_...): letters, digits, _ and -. */
    private const FRONT_ID = '/^[A-Za-z0-9_-]+$/';

    /** @var array<string, mixed> The webhook event without the message body (see slim()). */
    public array $data;

    /**
     * @param  array<string, mixed>  $data  The decoded webhook body.
     */
    public function __construct(array $data)
    {
        $this->data = self::slim($data);
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 120];
    }

    public function handle(): void
    {
        if (($this->data['type'] ?? null) !== 'inbound_received') {
            return;
        }

        $conversation = $this->data['payload']['conversation'];
        $conversationId = $conversation['id'];

        if (! is_string($conversationId) || preg_match(self::FRONT_ID, $conversationId) !== 1) {
            Log::warning('Front webhook - The event has no conversation id');

            return;
        }

        $message = $this->triggeringMessage($conversation);

        if ($message === null) {
            return;
        }

        $sender = self::addresses($message, 'from')[0] ?? null;
        $context = ['sender' => $sender, 'conversation_id' => $conversationId];

        if ($sender === null) {
            Log::info('Front webhook - The message has no sender', $context);

            return;
        }

        $senderUser = self::userWithEmail($sender);

        if ($reason = self::skipReason($message, $conversation, $sender, $senderUser)) {
            Log::info('Front webhook - Skipped ('.$reason.')', $context);

            return;
        }

        $user = $senderUser ?? self::ccUser($message);

        if ($user === null) {
            Log::info('Front webhook - No customer matches the sender or Cc', $context);

            return;
        }

        $filings = self::filingsFor($user);

        if ($filings->isEmpty()) {
            Log::info('Front webhook - The customer has no open lien filing', $context);

            return;
        }

        $frontUrl = 'https://app.frontapp.com/open/'.$conversationId;

        foreach ($filings as $filing) {
            $filing->recordCustomerEmail($conversationId, $frontUrl, $sender);
        }

        Log::info('Front webhook - Flagged lien filings', $context + ['filing_ids' => $filings->modelKeys()]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Front webhook - Job failed after retries', [
            'conversation_id' => $this->data['payload']['conversation']['id'] ?? null,
            'error' => $e->getMessage(),
        ]);
    }

    /**
     * What the job keeps of the webhook body: the conversation's id, subject
     * and last-message link, and the message's id, subject, recipients and
     * headers. Never the body, so none of it lands in the jobs table.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function slim(array $data): array
    {
        $event = is_array($data['payload'] ?? null) ? $data['payload'] : [];
        $conversation = is_array($event['conversation'] ?? null) ? $event['conversation'] : [];
        $message = is_array($event['target']['data'] ?? null) ? $event['target']['data'] : [];

        return [
            'type' => $data['type'] ?? null,
            'payload' => [
                'conversation' => [
                    'id' => $conversation['id'] ?? null,
                    'subject' => $conversation['subject'] ?? null,
                    'last_message' => $conversation['_links']['related']['last_message'] ?? null,
                ],
                'message' => self::messageFields($message),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $message
     * @return array{id: mixed, subject: mixed, recipients: array<int, mixed>, headers: mixed}
     */
    private static function messageFields(array $message): array
    {
        return [
            'id' => $message['id'] ?? null,
            'subject' => $message['subject'] ?? null,
            'recipients' => is_array($message['recipients'] ?? null) ? $message['recipients'] : [],
            'headers' => $message['headers'] ?? $message['metadata']['headers'] ?? null,
        ];
    }

    /**
     * The message that triggered the event: from the payload when it carries
     * the recipients, otherwise read back from the Front API (that message,
     * the conversation's last message, or its newest inbound one). Null,
     * logged, when it cannot be read.
     *
     * @param  array<string, mixed>  $conversation
     * @return array<string, mixed>|null
     */
    private function triggeringMessage(array $conversation): ?array
    {
        $message = $this->data['payload']['message'];

        if ($message['recipients'] !== []) {
            return $message;
        }

        if (blank(config('services.front.api_key'))) {
            Log::warning('Front webhook - The event has no sender and FRONT_API_KEY is not set', [
                'conversation_id' => $conversation['id'],
            ]);

            return null;
        }

        $messageId = $message['id'];

        if (! is_string($messageId) && is_string($conversation['last_message'])
            && preg_match('#/messages/([^/?]+)#', $conversation['last_message'], $matches) === 1) {
            $messageId = $matches[1];
        }

        if (is_string($messageId) && preg_match(self::FRONT_ID, $messageId) === 1) {
            return self::messageFields($this->frontGet("/messages/{$messageId}"));
        }

        $newestInbound = collect($this->frontGet("/conversations/{$conversation['id']}/messages")['_results'] ?? [])
            ->filter(fn ($item) => is_array($item) && ($item['is_inbound'] ?? false) === true)
            ->sortByDesc(fn (array $item) => $item['created_at'] ?? 0)
            ->first();

        if ($newestInbound === null) {
            Log::info('Front webhook - The conversation has no inbound message', ['conversation_id' => $conversation['id']]);

            return null;
        }

        return self::messageFields($newestInbound);
    }

    /**
     * GET from the Front API. Anything but a 2xx throws so the queue retries.
     *
     * @return array<string, mixed>
     */
    private function frontGet(string $path): array
    {
        $response = Http::withToken((string) config('services.front.api_key'))
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(10)
            ->get(rtrim((string) config('services.front.api_base'), '/').$path);

        if (! $response->successful()) {
            throw new \RuntimeException("Front API returned {$response->status()} for {$path}");
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    /**
     * Why this message is not a customer writing to us, or null.
     *
     * @param  array<string, mixed>  $message
     * @param  array<string, mixed>  $conversation
     */
    private static function skipReason(array $message, array $conversation, string $sender, ?User $senderUser): ?string
    {
        if (self::isOurAddress($sender)) {
            return 'our domain';
        }

        if (self::isAutomatedAddress($sender)) {
            return 'automated sender';
        }

        if ($senderUser !== null && self::isStaff($senderUser)) {
            return 'staff sender';
        }

        $subject = filled($message['subject']) ? $message['subject'] : $conversation['subject'];

        if (Str::startsWith(Str::lower(ltrim((string) $subject)), self::AUTO_REPLY_SUBJECTS)) {
            return 'auto-reply subject';
        }

        $autoSubmitted = self::header($message, 'Auto-Submitted');

        if (filled($autoSubmitted) && Str::lower($autoSubmitted) !== 'no') {
            return 'auto-submitted';
        }

        return null;
    }

    /**
     * The first Cc address that belongs to a customer.
     *
     * @param  array<string, mixed>  $message
     */
    private static function ccUser(array $message): ?User
    {
        foreach (self::addresses($message, 'cc') as $address) {
            if (self::isOurAddress($address) || self::isAutomatedAddress($address)) {
                continue;
            }

            $user = self::userWithEmail($address);

            if ($user !== null && ! self::isStaff($user)) {
                return $user;
            }
        }

        return null;
    }

    /**
     * The user's businesses' filings waiting on them; when none, their most
     * recently updated open filing (paid and on the lien board).
     *
     * @return Collection<int, LienFiling>
     */
    private static function filingsFor(User $user): Collection
    {
        $businessIds = $user->businesses()->pluck('businesses.id');

        if ($businessIds->isEmpty()) {
            return new Collection;
        }

        $filings = fn (): Builder => LienFiling::query()
            ->withoutGlobalScope('business')
            ->whereIn('business_id', $businessIds);

        $waitingStatuses = array_values(array_filter(
            FilingStatus::cases(),
            fn (FilingStatus $status): bool => $status->isWaitingOnCustomer(),
        ));

        $waiting = $filings()->whereIn('status', $waitingStatuses)->get();

        if ($waiting->isNotEmpty()) {
            return $waiting;
        }

        $open = $filings()
            ->whereNotNull('paid_at')
            ->whereNotIn('status', KanbanColumn::offBoardStatuses())
            ->latest('updated_at')
            ->orderByDesc('id')
            ->first();

        return new Collection($open ? [$open] : []);
    }

    /**
     * Lowercased addresses of one recipient role (from, to, cc, ...), in order.
     *
     * @param  array<string, mixed>  $message
     * @return list<string>
     */
    private static function addresses(array $message, string $role): array
    {
        return collect($message['recipients'] ?? [])
            ->filter(fn ($recipient) => is_array($recipient) && ($recipient['role'] ?? null) === $role)
            ->map(fn (array $recipient) => Str::lower(trim((string) ($recipient['handle'] ?? ''))))
            ->filter(fn (string $address) => str_contains($address, '@'))
            ->values()
            ->all();
    }

    /**
     * A header's value, or null. Front may list headers as {name: value} or
     * as [{name, value}].
     *
     * @param  array<string, mixed>  $message
     */
    private static function header(array $message, string $name): ?string
    {
        if (! is_array($message['headers'] ?? null)) {
            return null;
        }

        foreach ($message['headers'] as $key => $value) {
            if (is_array($value)) {
                $key = $value['name'] ?? $key;
                $value = $value['value'] ?? null;
            }

            if (is_string($key) && strcasecmp(str_replace('_', '-', $key), $name) === 0 && is_scalar($value)) {
                return trim((string) $value);
            }
        }

        return null;
    }

    private static function isOurAddress(string $address): bool
    {
        $domain = Str::afterLast($address, '@');

        foreach (self::OUR_DOMAINS as $ours) {
            if ($domain === $ours || str_ends_with($domain, '.'.$ours)) {
                return true;
            }
        }

        return false;
    }

    private static function isAutomatedAddress(string $address): bool
    {
        return in_array(Str::beforeLast($address, '@'), self::AUTOMATED_LOCAL_PARTS, true);
    }

    /**
     * Our own people hold a role or a direct permission; customers hold
     * neither (as on the lien board, BoardCard).
     */
    private static function isStaff(User $user): bool
    {
        return $user->roles()->exists() || $user->permissions()->exists();
    }

    private static function userWithEmail(string $address): ?User
    {
        return User::query()->whereRaw('LOWER(email) = ?', [Str::lower($address)])->first();
    }
}
