<?php

namespace App\Domains\Forms;

use InvalidArgumentException;

class FormTypeConfig
{
    /**
     * Get configuration for a specific form type.
     *
     * @throws InvalidArgumentException
     */
    public static function get(string $formType): array
    {
        $config = config("form_types.{$formType}");

        if (! $config) {
            throw new InvalidArgumentException("Unknown form type: {$formType}");
        }

        return $config;
    }

    /**
     * Get all form type configurations.
     *
     * @return array<string, array>
     */
    public static function all(): array
    {
        return config('form_types', []);
    }

    /**
     * Get the definition directory name for a form type.
     */
    public static function definitionDir(string $formType): string
    {
        return static::get($formType)['definition_dir'];
    }

    /**
     * Check if a form type exists.
     */
    public static function exists(string $formType): bool
    {
        return config("form_types.{$formType}") !== null;
    }

    /**
     * Get the billing type for a form type.
     */
    public static function billingType(string $formType): string
    {
        return static::get($formType)['billing_type'];
    }

    /**
     * Get the state mode for a form type.
     */
    public static function stateMode(string $formType): string
    {
        return static::get($formType)['state_mode'];
    }

    /**
     * Get the max states for a form type.
     */
    public static function maxStates(string $formType): int
    {
        $config = static::get($formType);

        return $config['max_states'] ?? ($config['state_mode'] === 'single' ? 1 : 40);
    }

    /**
     * Check if form type uses subscription billing.
     */
    public static function isSubscription(string $formType): bool
    {
        return static::billingType($formType) === 'subscription';
    }

    /**
     * Get the subscription name for a subscription-based form type.
     */
    public static function subscriptionName(string $formType): ?string
    {
        $config = static::get($formType);

        return $config['subscription_name'] ?? null;
    }

    /**
     * Get available form type keys (slugs).
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(static::all());
    }
}
