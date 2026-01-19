<?php

namespace App\Domains\Forms\Engine\Validation;

interface CrossFieldValidator
{
    /**
     * Get the unique name/key of this validator
     */
    public function name(): string;

    /**
     * Validate the data
     *
     * @param  array  $data  The data to validate
     * @param  string  $field  The field path this validator is for
     * @param  array  $options  Additional options from the definition
     * @return array Array of error messages keyed by field path, empty if valid
     */
    public function validate(array $data, string $field, array $options = []): array;
}
