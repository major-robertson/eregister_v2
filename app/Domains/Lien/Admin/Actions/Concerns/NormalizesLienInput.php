<?php

namespace App\Domains\Lien\Admin\Actions\Concerns;

/**
 * Defensive input normalization shared by the admin edit Actions, so each Action
 * owns the shape of its input independent of the Livewire form that calls it
 * (a future caller can't smuggle in untrimmed strings, lowercase state codes,
 * or empty-string-instead-of-null money).
 */
trait NormalizesLienInput
{
    protected function nullIfBlank(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function normalizeState(mixed $value): ?string
    {
        $state = $this->nullIfBlank($value);

        return $state === null ? null : strtoupper(substr($state, 0, 2));
    }

    protected function normalizeEmail(mixed $value): ?string
    {
        $email = $this->nullIfBlank($value);

        return $email === null ? null : strtolower($email);
    }

    /**
     * Money already arrives as cents from the form layer; cast defensively to a
     * nullable int (blank/'' -> null) so callers can't persist '' or a float.
     */
    protected function asCents(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * Date already arrives as a 'Y-m-d' string (or blank). Normalize blank -> null;
     * the model's date cast handles parsing on save.
     */
    protected function nullableDate(mixed $value): ?string
    {
        return $this->nullIfBlank($value);
    }
}
