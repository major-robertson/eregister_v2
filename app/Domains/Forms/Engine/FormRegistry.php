<?php

namespace App\Domains\Forms\Engine;

use App\Domains\Forms\FormTypeConfig;

class FormRegistry
{
    /**
     * In-memory cache for loaded definitions within a single request.
     *
     * @var array<string, array>
     */
    private array $cache = [];

    public function __construct(
        private DefinitionMerger $merger,
        private DrivesConditionalDetector $driversDetector,
    ) {}

    /**
     * Get base definition for a form type.
     *
     * @param  string  $formType  The canonical form type slug (e.g., 'sales_tax_permit')
     */
    public function getBase(string $formType): array
    {
        $cacheKey = "base:{$formType}";

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $definitionDir = FormTypeConfig::definitionDir($formType);
        $path = $this->getDefinitionPath($definitionDir, 'base');

        if (! file_exists($path)) {
            throw new \InvalidArgumentException("Form definition not found: {$definitionDir}/base.php");
        }

        // Run the auto-detector on the base too. The runtime drives
        // most navigation through the merged definition, but
        // getBase() is also used directly by tests and by the field
        // dispatcher in some flows — so the flag must be present
        // regardless of which path loaded it.
        return $this->cache[$cacheKey] = $this->driversDetector->detect(require $path);
    }

    /**
     * Get merged definition for a form type and state.
     *
     * @param  string  $formType  The canonical form type slug (e.g., 'sales_tax_permit')
     * @param  string  $stateCode  The state code (e.g., 'CA')
     */
    public function get(string $formType, string $stateCode): array
    {
        $cacheKey = "merged:{$formType}:{$stateCode}";

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $definitionDir = FormTypeConfig::definitionDir($formType);
        $base = $this->getBase($formType);
        $statePath = $this->getDefinitionPath($definitionDir, $stateCode);

        if (! file_exists($statePath)) {
            return $this->cache[$cacheKey] = $base;
        }

        $override = require $statePath;

        $merged = $this->merger->merge($base, $override);

        return $this->cache[$cacheKey] = $this->driversDetector->detect($merged);
    }

    /**
     * Get available form type slugs from config.
     *
     * @return array<int, string>
     */
    public function getFormTypes(): array
    {
        return FormTypeConfig::keys();
    }

    private function getDefinitionPath(string $definitionDir, string $file): string
    {
        return app_path("Domains/Forms/Definitions/{$definitionDir}/{$file}.php");
    }
}
