<?php

namespace App\Domains\Forms\Engine\Validation;

use Illuminate\Validation\ValidationException;

class CrossFieldValidatorRegistry
{
    /** @var array<string, CrossFieldValidator> */
    private array $validators = [];

    public function register(CrossFieldValidator $validator): void
    {
        $this->validators[$validator->name()] = $validator;
    }

    public function get(string $name): ?CrossFieldValidator
    {
        return $this->validators[$name] ?? null;
    }

    /**
     * Validate using a named validator
     *
     * @return array Array of error messages keyed by field path
     */
    public function validate(string $rule, array $data, string $field, array $options = []): array
    {
        $validator = $this->get($rule);

        if (! $validator) {
            return [];
        }

        return $validator->validate($data, $field, $options);
    }

    /**
     * Validate and throw ValidationException with Livewire-prefixed keys
     *
     * @throws ValidationException
     */
    public function validateWithPrefix(
        string $rule,
        array $data,
        string $field,
        array $options,
        string $prefix
    ): void {
        $errors = $this->validate($rule, $data, $field, $options);

        if (! empty($errors)) {
            $prefixedErrors = [];
            foreach ($errors as $key => $messages) {
                $prefixedKey = "{$prefix}.{$key}";
                $prefixedErrors[$prefixedKey] = (array) $messages;
            }

            throw ValidationException::withMessages($prefixedErrors);
        }
    }

    /**
     * Get all registered validators
     *
     * @return array<string, CrossFieldValidator>
     */
    public function all(): array
    {
        return $this->validators;
    }
}
