<?php

namespace App\Domains\ResaleCert\Pdf;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use Illuminate\Support\Facades\Log;

class StateCertificateFactory
{
    /** @var array<string, StateCertificateInterface> */
    protected array $certificates = [];

    /** @var array<string, array{class: class-string, template: string, name: string, template_out_of_state?: string}> */
    protected array $config;

    public function __construct()
    {
        $this->config = config('resale_cert.states', []);

        foreach ($this->config as $stateCode => $stateConfig) {
            if (isset($stateConfig['class'])) {
                $this->certificates[$stateCode] = new $stateConfig['class'];
            }
        }
    }

    public function make(string $stateCode): StateCertificateInterface
    {
        if (! $this->has($stateCode)) {
            Log::error('No resale certificate implementation for state', [
                'state_code' => $stateCode,
                'available_states' => array_keys($this->certificates),
            ]);

            throw new \RuntimeException("No certificate implementation for state: {$stateCode}");
        }

        return $this->certificates[$stateCode];
    }

    public function has(string $stateCode): bool
    {
        return isset($this->certificates[$stateCode]);
    }

    /**
     * Template path (relative to resources/) for the given state, or null
     * for custom-drawn states.
     */
    public function getTemplatePath(string $stateCode): ?string
    {
        if (! $this->has($stateCode) || empty($this->config[$stateCode]['template'])) {
            return null;
        }

        return config('resale_cert.templates_path').'/'.$this->config[$stateCode]['template'];
    }

    /**
     * Template path for a specific certificate — some states (GA, NJ) provide
     * a separate form when the purchaser's tax ID is from another state.
     */
    public function getTemplatePathForCertificate(string $stateCode, ?ResaleCertificate $certificate = null): ?string
    {
        if (! $this->has($stateCode)) {
            return null;
        }

        $stateConfig = $this->config[$stateCode];

        if ($certificate && ! empty($stateConfig['template_out_of_state'])) {
            $taxIdSourceState = $certificate->business_snapshot['tax_id_source_state'] ?? null;

            if ($taxIdSourceState !== $stateCode) {
                return config('resale_cert.templates_path').'/'.$stateConfig['template_out_of_state'];
            }
        }

        return $this->getTemplatePath($stateCode);
    }

    /** @return list<string> */
    public function getRegisteredStates(): array
    {
        return array_keys($this->certificates);
    }

    public function getStateName(string $stateCode): ?string
    {
        return $this->config[$stateCode]['name'] ?? null;
    }
}
