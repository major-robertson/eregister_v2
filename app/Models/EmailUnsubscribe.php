<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailUnsubscribe extends Model
{
    use HasFactory;

    public $timestamps = false;

    const CATEGORY_ABANDON_CHECKOUT = 'abandon_checkout';

    const CATEGORY_MARKETING = 'marketing';

    /**
     * All available unsubscribe categories with human-readable labels.
     *
     * @var array<string, string>
     */
    public static array $categories = [
        self::CATEGORY_ABANDON_CHECKOUT => 'Order Reminders',
        self::CATEGORY_MARKETING => 'Marketing & Promotions',
    ];

    protected $fillable = [
        'user_id',
        'category',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a user is unsubscribed from a category (or from all emails).
     */
    public static function isUnsubscribed(User $user, string $category): bool
    {
        if ($user->unsubscribed_from_all_emails_at !== null) {
            return true;
        }

        return static::where('user_id', $user->getKey())
            ->where('category', $category)
            ->exists();
    }

    /**
     * Unsubscribe a user from a specific category.
     */
    public static function unsubscribe(User $user, string $category): void
    {
        static::firstOrCreate([
            'user_id' => $user->getKey(),
            'category' => $category,
        ], [
            'created_at' => now(),
        ]);
    }

    /**
     * Unsubscribe a user from all optional emails.
     */
    public static function unsubscribeFromAll(User $user): void
    {
        $user->update(['unsubscribed_from_all_emails_at' => now()]);
    }

    /**
     * Resubscribe a user to a specific category.
     */
    public static function resubscribe(User $user, string $category): void
    {
        static::where('user_id', $user->getKey())
            ->where('category', $category)
            ->delete();
    }

    /**
     * Resubscribe a user to all emails (clears master opt-out).
     */
    public static function resubscribeToAll(User $user): void
    {
        $user->update(['unsubscribed_from_all_emails_at' => null]);
        static::where('user_id', $user->getKey())->delete();
    }
}
