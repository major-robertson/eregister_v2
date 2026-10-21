<?php

namespace App\Domains\Lien\Admin\Support;

use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * One card on the lien board: the filing, its column, the last thing we did
 * on it and, when it has waited on us too long, the red alert. LienBoard
 * renders these and lien:agent-digest emails the red ones, so the board and
 * the email always agree.
 */
final class BoardCard
{
    /** "No action for n business days" turns red past this many. */
    public const IDLE_BUSINESS_DAYS = 2;

    /**
     * A customer's answer with nothing from us since, or their email with no
     * status change or "Still waiting", turns red at this many.
     */
    public const ANSWER_BUSINESS_DAYS = 1;

    /** Columns where the next move is ours, so an idle card turns red. */
    private const AGED_COLUMNS = [
        KanbanColumn::New,
        KanbanColumn::InProgress,
        KanbanColumn::NeedsReview,
        KanbanColumn::ReadyToFile,
    ];

    private function __construct(
        public readonly LienFiling $filing,
        public readonly KanbanColumn $column,
        public readonly ?CarbonInterface $lastActionAt,
        public readonly ?CarbonInterface $customerRepliedAt,
        public readonly ?string $alert,
    ) {}

    /**
     * Every card on the board, keyed by filing id, in board order: the
     * paid, active filings (not Draft, Awaiting Payment, Complete, Canceled,
     * Refunded, nor deleted).
     *
     * @return Collection<int, self>
     */
    public static function all(): Collection
    {
        $filings = LienFiling::query()
            ->withoutGlobalScope('business')
            ->whereNotNull('paid_at')
            ->whereNotIn('status', KanbanColumn::offBoardStatuses())
            ->with(self::relations())
            ->withExists(['events as dates_changed_after_deadline_block_exists' => fn ($q) => $q->where('event_type', LienFilingEvent::DATES_CHANGED_AFTER_DEADLINE_BLOCK)])
            ->get();

        $staffIds = self::staffUserIds();

        return self::sorted($filings)
            ->map(fn (LienFiling $filing) => self::make($filing, $staffIds))
            ->keyBy(fn (self $card) => $card->filing->id);
    }

    /**
     * The card for one filing, or null when the board leaves it out. Loads
     * onto a copy, so the caller's loaded relations (the filing page's
     * activity events) stay as they were.
     */
    public static function forFiling(LienFiling $filing): ?self
    {
        if ($filing->paid_at === null || $filing->trashed() || KanbanColumn::tryForFiling($filing) === null) {
            return null;
        }

        return self::make((clone $filing)->load(self::relations()), self::staffUserIds());
    }

    public function isRed(): bool
    {
        return $this->alert !== null;
    }

    /**
     * Why the filing is on hold: the note on the status change into Hold.
     */
    public function holdReason(): ?string
    {
        if ($this->column !== KanbanColumn::Hold) {
            return null;
        }

        $intoHold = $this->filing->events->first(
            fn ($event) => $event->event_type === 'status_changed' && ($event->payload_json['to'] ?? null) === 'hold'
        );

        return filled($intoHold?->meta('note')) ? $intoHold->meta('note') : null;
    }

    /**
     * The start of the open request we sent the customer.
     */
    public function requestPreview(): ?string
    {
        $text = $this->filing->openClientRequest()['text'] ?? null;

        return filled($text) ? Str::limit($text, 80) : null;
    }

    /**
     * Whether the customer answered after our latest request (or answered
     * at all, when the request was sent before requests were stored).
     */
    public function customerAnswered(): bool
    {
        if ($this->customerRepliedAt === null) {
            return false;
        }

        $sentAt = $this->filing->clientRequestSentAt();

        return $sentAt === null || $this->customerRepliedAt->gt($sentAt);
    }

    /**
     * What a card needs loaded on its filing.
     *
     * @return array<int|string, mixed>
     */
    private static function relations(): array
    {
        return [
            'project',
            'project.business',
            'documentType',
            'createdBy',
            'projectDeadline',
            'latestCustomerReply',
            // Newest first: our actions, and the note the card shows.
            'events' => fn ($query) => $query
                ->whereIn('event_type', ['status_changed', 'note_added', 'client_request_sent', 'reply_dismissed'])
                ->latest()
                ->orderByDesc('id'),
        ];
    }

    /**
     * @param  list<int>  $staffIds
     */
    private static function make(LienFiling $filing, array $staffIds): self
    {
        // Our actions: a note, a request to the customer, "Still waiting" on
        // their email, or a status change one of us made. The payment
        // webhook, the customer's answer and a signer's e-signature log
        // status changes too, and those don't count.
        $lastActionAt = $filing->events->first(
            fn ($event) => $event->event_type !== 'status_changed' || in_array((int) $event->created_by, $staffIds, true)
        )?->created_at;

        $column = KanbanColumn::forFiling($filing);

        // Full-service orders move to In Fulfillment by themselves at payment
        // (LienPaymentService), so the status alone can't tell a new order
        // from one we are working on. Until one of us acts on it, it is New.
        if ($column === KanbanColumn::InProgress && $lastActionAt === null) {
            $column = KanbanColumn::New;
        }

        $repliedAt = $filing->customerRepliedAt();

        return new self($filing, $column, $lastActionAt, $repliedAt, self::alertFor($filing, $column, $lastActionAt, $repliedAt));
    }

    private static function alertFor(LienFiling $filing, KanbanColumn $column, ?CarbonInterface $lastActionAt, ?CarbonInterface $repliedAt): ?string
    {
        // The customer emailed us and no one has changed the status or
        // marked it "Still waiting". A note does not clear this one.
        if ($filing->hasCustomerEmail()) {
            $days = self::businessDaysBetween($filing->customer_emailed_at, now());

            if ($days >= self::ANSWER_BUSINESS_DAYS) {
                return "Customer's email waiting {$days} ".Str::plural('business day', $days);
            }
        }

        // The customer answered and nothing from us has followed.
        if ($repliedAt !== null && ($lastActionAt === null || $repliedAt->gt($lastActionAt))) {
            $days = self::businessDaysBetween($repliedAt, now());

            return $days >= self::ANSWER_BUSINESS_DAYS
                ? "Customer's answer waiting {$days} ".Str::plural('business day', $days)
                : null;
        }

        if (! in_array($column, self::AGED_COLUMNS, true)) {
            return null;
        }

        $days = self::businessDaysBetween($lastActionAt ?? $filing->paid_at ?? $filing->created_at, now());

        return $days > self::IDLE_BUSINESS_DAYS ? "No action for {$days} business days" : null;
    }

    /**
     * Within a column: filings the customer emailed us about first, then rush
     * orders (expedite, then rush, soonest submit-by date first, an added
     * Rush with no deadline known last), then the nearest deadline (none
     * last), then the oldest payment.
     *
     * TODO EREG-138 merge: sort by lien_filings.must_submit_by (nulls last)
     * instead of the project's lien deadline.
     *
     * @param  Collection<int, LienFiling>  $filings
     * @return Collection<int, LienFiling>
     */
    private static function sorted(Collection $filings): Collection
    {
        $rushRank = fn (LienFiling $filing): int => match ($filing->rush_tier) {
            'expedite' => 0,
            'rush' => 1,
            default => 2,
        };

        return $filings->sort(function (LienFiling $a, LienFiling $b) use ($rushRank): int {
            if ($a->hasCustomerEmail() !== $b->hasCustomerEmail()) {
                return $a->hasCustomerEmail() ? -1 : 1;
            }

            if ($rushRank($a) !== $rushRank($b)) {
                return $rushRank($a) <=> $rushRank($b);
            }

            if ($rushRank($a) < 2 && $a->must_submit_by?->getTimestamp() !== $b->must_submit_by?->getTimestamp()) {
                if ($a->must_submit_by === null || $b->must_submit_by === null) {
                    return $a->must_submit_by === null ? 1 : -1;
                }

                return $a->must_submit_by->getTimestamp() <=> $b->must_submit_by->getTimestamp();
            }

            $dueA = $a->projectDeadline?->due_date;
            $dueB = $b->projectDeadline?->due_date;

            if (($dueA === null) !== ($dueB === null)) {
                return $dueA === null ? 1 : -1;
            }

            return [$dueA?->getTimestamp(), $a->paid_at?->getTimestamp(), $a->id]
                <=> [$dueB?->getTimestamp(), $b->paid_at?->getTimestamp(), $b->id];
        })->values();
    }

    /**
     * Our own people: anyone holding a role or a direct permission (the
     * admin roles board grants them; customers hold neither).
     *
     * @return list<int>
     */
    private static function staffUserIds(): array
    {
        return User::query()
            ->where(fn (Builder $query) => $query->whereHas('roles')->orWhereHas('permissions'))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * Weekdays between two moments by Eastern calendar date: those after the
     * first date, up to and including the second. Monday to Wednesday is 2,
     * Friday to Monday is 1. No holidays.
     *
     * TODO EREG-138 merge: use BusinessDays
     */
    private static function businessDaysBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        $day = CarbonImmutable::parse($from->toImmutable()->eastern()->toDateString());
        $end = CarbonImmutable::parse($to->toImmutable()->eastern()->toDateString());
        $days = 0;

        while ($day->lt($end)) {
            $day = $day->addDay();
            $days += $day->isWeekend() ? 0 : 1;
        }

        return $days;
    }
}
