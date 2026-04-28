<?php

namespace App\Support\Workspaces;

use App\Domains\Business\Models\Business;

final readonly class Workspace
{
    /**
     * @param  array<int, array{label: string, icon: string, route: string, current_pattern: string}>  $nav
     */
    public function __construct(
        public string $key,
        public string $name,
        public string $slug,
        public string $description,
        public string $icon,
        public string $badge,
        public string $badgeColor,
        public string $bgClass,
        public string $dashboardRoute,
        public bool $enabled,
        public ?string $formType,
        public string $dataResolver,
        public array $nav,
        public string $navHeading,
    ) {}

    /**
     * @param  array<string, mixed>  $entry
     */
    public static function fromConfig(string $key, array $entry): self
    {
        return new self(
            key: $key,
            name: $entry['name'],
            slug: $entry['slug'],
            description: $entry['description'] ?? '',
            icon: $entry['icon'] ?? 'squares-2x2',
            badge: $entry['badge'] ?? $entry['name'],
            badgeColor: $entry['badge_color'] ?? 'zinc',
            bgClass: $entry['bg_class'] ?? 'bg-zinc-50',
            dashboardRoute: $entry['dashboard_route'],
            enabled: (bool) ($entry['enabled'] ?? true),
            formType: $entry['form_type'] ?? null,
            dataResolver: $entry['data_resolver'],
            nav: $entry['nav'] ?? [],
            navHeading: $entry['nav_heading'] ?? $entry['name'],
        );
    }

    public function dataResolver(): WorkspaceDataResolver
    {
        return app($this->dataResolver);
    }

    public function cardState(Business $business): WorkspaceCardState
    {
        $resolver = $this->dataResolver();
        $hasData = $resolver->hasData($business);

        return new WorkspaceCardState(
            hasData: $hasData,
            summary: $resolver->summary($business),
            ctaLabel: $hasData ? 'Open' : 'Get Started',
        );
    }

    public function isCurrent(): bool
    {
        foreach ($this->nav as $item) {
            if (request()->routeIs($item['current_pattern'])) {
                return true;
            }
        }

        return false;
    }
}
