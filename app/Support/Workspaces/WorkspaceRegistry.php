<?php

namespace App\Support\Workspaces;

use App\Domains\Business\Models\Business;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class WorkspaceRegistry
{
    /** @var array<string, Workspace>|null */
    private ?array $cache = null;

    public function __construct(private readonly ConfigRepository $config) {}

    /**
     * @return array<string, Workspace>
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $entries = $this->config->get('workspaces', []);

        $this->cache = [];
        foreach ($entries as $key => $entry) {
            $this->cache[$key] = Workspace::fromConfig($key, $entry);
        }

        return $this->cache;
    }

    public function find(string $key): ?Workspace
    {
        return $this->all()[$key] ?? null;
    }

    /**
     * Look up the workspace that owns the given forms-runner form type
     * (e.g. `sales_tax_permit` -> the Sales Tax workspace). Returns null
     * if no workspace claims this form type, in which case callers should
     * fall back to the generic `layouts.app` shell.
     */
    public function findByFormType(string $formType): ?Workspace
    {
        foreach ($this->all() as $workspace) {
            if ($workspace->formType === $formType) {
                return $workspace;
            }
        }

        return null;
    }

    /**
     * Workspaces enabled for the given business. Currently filters on the
     * config-level `enabled` bool; per-business gating logic can be added
     * here later (entitlements, role checks, feature flags, etc.).
     *
     * @return array<string, Workspace>
     */
    public function enabledFor(Business $business): array
    {
        return array_filter(
            $this->all(),
            fn (Workspace $workspace): bool => $workspace->enabled,
        );
    }
}
