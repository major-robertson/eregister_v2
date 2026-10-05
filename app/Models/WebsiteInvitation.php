<?php

namespace App\Models;

use App\Domains\Business\Models\Business;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A customer invited to Happy Websites by email (email:send-websites-intro):
 * the first email, the date their spot is held through, the reminder, and
 * whether they answered.
 */
class WebsiteInvitation extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'message_id',
        'invited_at',
        'deadline_on',
        'reminded_at',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'deadline_on' => 'immutable_date',
            'reminded_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * The date a spot is held through for an email sent at $sentAt: the
     * Friday of the following week, on the Eastern calendar. That is 7 to 11
     * days after a weekday send, and the email states it, so it is a promise.
     * Don't change the rule for invitations already sent.
     */
    public static function deadlineFor(CarbonInterface $sentAt): CarbonImmutable
    {
        return CarbonImmutable::instance($sentAt)
            ->setTimezone(config('app.display_timezone'))
            ->startOfWeek(CarbonInterface::MONDAY)
            ->addWeek()
            ->addDays(4)
            ->startOfDay();
    }

    /** The reminder goes out 3 days before the date: the Tuesday of that week. */
    public function reminderOn(): CarbonImmutable
    {
        return $this->deadline_on->subDays(3);
    }

    /** "Friday, October 16", as both emails print it. */
    public function deadlineForHumans(): string
    {
        return $this->deadline_on->format('l, F j');
    }
}
