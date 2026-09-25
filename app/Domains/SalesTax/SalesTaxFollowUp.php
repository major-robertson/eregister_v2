<?php

namespace App\Domains\SalesTax;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Models\FormApplication;
use App\Models\EmailSequence;
use App\Models\User;
use App\Support\SignupIntent;

/**
 * Starts the sales tax follow-up emails: the sales_tax_started sequence,
 * whose timing and stop rules live on EmailSequence. Someone who signed up
 * through a sales tax door and has not picked a state yet gets a reminder
 * an hour later, a day after that, and three days after that. Picking a
 * state opens the order screen, whose own reminders (abandon_checkout) take
 * over. One series per person, ever.
 */
class SalesTaxFollowUp
{
    public const SEQUENCE = 'sales_tax_started';

    /**
     * Called right after registration, while the sign-up intent is still in
     * the session.
     */
    public static function onSignup(User $user): ?EmailSequence
    {
        $product = SignupIntent::product();

        // The door they clicked wins: a resale page visitor who chose a sales
        // tax door wants a registration, and a sales tax page visitor who
        // chose the certificate door gets the resale follow-ups instead.
        $cameForRegistration = $product !== null
            ? $product === 'sales-tax'
            : $user->signedUpFromSalesTax();

        return $cameForRegistration ? static::start($user, state: SignupIntent::state()) : null;
    }

    /**
     * Also used for one-off backfills. Skips anyone who already has a sales
     * tax application: the order screen's reminders cover them. $state is
     * the state they picked on the marketing page; the reminder link
     * preselects it.
     */
    public static function start(User $user, ?Business $business = null, ?string $state = null): ?EmailSequence
    {
        if (static::hasApplication($user)) {
            return null;
        }

        return EmailSequence::firstOrCreate(
            [
                'sequence_type' => self::SEQUENCE,
                'sequenceable_type' => $user->getMorphClass(),
                'sequenceable_id' => $user->getKey(),
            ],
            [
                'user_id' => $user->getKey(),
                'business_id' => $business?->getKey(),
                'customer_type' => $business ? EmailSequence::detectCustomerType($business) : 'new',
                'resume_url' => route('sales-tax.registrations.start', array_filter(['state' => static::registrableState($state)])),
                'next_send_at' => now()->addMinutes(
                    (new EmailSequence(['sequence_type' => self::SEQUENCE]))->config()['delays'][0]
                ),
            ]
        );
    }

    /**
     * Whether any of the user's businesses has a sales tax application, paid
     * or not. Picking a state creates one on the way to the order screen.
     */
    public static function hasApplication(User $user): bool
    {
        return FormApplication::query()
            ->where('form_type', 'sales_tax_permit')
            ->whereIn('business_id', $user->businesses()->allRelatedIds())
            ->exists();
    }

    /**
     * The state code when the state picker offers it, else null. States
     * with no sales tax (Delaware, Oregon...) are never named or preselected.
     */
    public static function registrableState(?string $code): ?string
    {
        $code = strtoupper((string) $code);
        $definition = app(FormRegistry::class)->getBase('sales_tax_permit');

        if (! in_array($code, $definition['available_states'] ?? [], true)
            || array_key_exists($code, $definition['excluded_states'] ?? [])) {
            return null;
        }

        return $code;
    }
}
