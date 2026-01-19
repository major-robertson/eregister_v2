<?php

namespace App\Domains\Forms\Engine;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;

class SensitiveDataProtector
{
    public function __construct(
        private Encrypter $encrypter
    ) {}

    /**
     * Encrypt core data using base definition
     */
    public function encryptCoreData(array $data, array $baseDefinition): array
    {
        $sensitiveFields = $this->extractSensitiveFields($baseDefinition['core_steps'] ?? []);

        return $this->processData($data, $sensitiveFields, 'encrypt');
    }

    /**
     * Decrypt core data using base definition
     */
    public function decryptCoreData(array $data, array $baseDefinition): array
    {
        $sensitiveFields = $this->extractSensitiveFields($baseDefinition['core_steps'] ?? []);

        return $this->processData($data, $sensitiveFields, 'decrypt');
    }

    /**
     * Encrypt state data using state's merged definition
     */
    public function encryptStateData(array $data, array $mergedStateDefinition): array
    {
        $sensitiveFields = $this->extractSensitiveFields($mergedStateDefinition['state_steps'] ?? []);

        return $this->processData($data, $sensitiveFields, 'encrypt');
    }

    /**
     * Decrypt state data using state's merged definition
     */
    public function decryptStateData(array $data, array $mergedStateDefinition): array
    {
        $sensitiveFields = $this->extractSensitiveFields($mergedStateDefinition['state_steps'] ?? []);

        return $this->processData($data, $sensitiveFields, 'decrypt');
    }

    /**
     * Process wildcard paths including:
     * - responsible_people.*.ssn_last4 (numeric array)
     * - responsible_people_extra.*.ca_driver_license (UUID-keyed map)
     * - nested.*.deeply.*.field (multi-level)
     */
    private function processData(array $data, array $sensitiveFields, string $mode): array
    {
        foreach ($sensitiveFields as $path) {
            $data = $this->processPath($data, explode('.', $path), $mode);
        }

        return $data;
    }

    private function processPath(array $data, array $segments, string $mode, string $currentPath = ''): array
    {
        if (empty($segments)) {
            return $data;
        }

        $segment = array_shift($segments);

        if ($segment === '*') {
            // Wildcard: iterate all keys (numeric OR string/UUID)
            $target = $currentPath ? data_get($data, $currentPath) : $data;

            if (! is_array($target)) {
                return $data;
            }

            foreach (array_keys($target) as $key) {
                $newPath = $currentPath ? "{$currentPath}.{$key}" : (string) $key;

                if (empty($segments)) {
                    // Leaf: this is the field to encrypt/decrypt
                    $value = data_get($data, $newPath);
                    if (is_string($value) && $value !== '') {
                        data_set($data, $newPath, $this->transform($value, $mode));
                    }
                } else {
                    // Continue recursion with remaining segments
                    $data = $this->processPath($data, $segments, $mode, $newPath);
                }
            }
        } else {
            $newPath = $currentPath ? "{$currentPath}.{$segment}" : $segment;

            if (empty($segments)) {
                // Leaf: encrypt/decrypt this field
                $value = data_get($data, $newPath);
                if (is_string($value) && $value !== '') {
                    data_set($data, $newPath, $this->transform($value, $mode));
                }
            } else {
                $data = $this->processPath($data, $segments, $mode, $newPath);
            }
        }

        return $data;
    }

    private function transform(string $value, string $mode): string
    {
        if ($mode === 'encrypt') {
            return $this->encrypter->encryptString($value);
        }

        try {
            return $this->encrypter->decryptString($value);
        } catch (DecryptException) {
            // Already decrypted or invalid - return as-is
            return $value;
        }
    }

    /**
     * Extract sensitive field paths from step definitions
     */
    private function extractSensitiveFields(array $steps): array
    {
        $fields = [];

        foreach ($steps as $step) {
            foreach ($step['fields'] ?? [] as $fieldKey => $field) {
                if ($field['sensitive'] ?? false) {
                    $fields[] = $fieldKey;
                }

                // Repeater schema
                foreach ($field['schema'] ?? [] as $subKey => $subField) {
                    if ($subField['sensitive'] ?? false) {
                        $fields[] = "{$fieldKey}.*.{$subKey}";
                    }
                }

                // person_state_extra type - special handling
                if (($field['type'] ?? '') === 'person_state_extra') {
                    foreach ($field['schema'] ?? [] as $subKey => $subField) {
                        if ($subField['sensitive'] ?? false) {
                            $fields[] = "responsible_people_extra.*.{$subKey}";
                        }
                    }
                }
            }
        }

        return $fields;
    }
}
