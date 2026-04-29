<?php

namespace App\Support\Workspaces;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;

final readonly class Workspace
{
    /**
     * @param  array<int, string>  $formTypes
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
        public array $formTypes,
        public ?string $startRouteName,
        public ?string $applicationRouteName,
        public ?string $startRouteParam,
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
            formTypes: (array) ($entry['form_types'] ?? []),
            startRouteName: $entry['start_route_name'] ?? null,
            applicationRouteName: $entry['application_route_name'] ?? null,
            startRouteParam: $entry['start_route_param'] ?? null,
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

    /**
     * Whether this workspace owns the given forms-runner form type.
     */
    public function claimsFormType(string $formType): bool
    {
        return in_array($formType, $this->formTypes, true);
    }

    /**
     * The first form type this workspace claims, used as the default
     * target for day-one CTAs that don't yet need a type picker.
     */
    public function primaryFormType(): ?string
    {
        return $this->formTypes[0] ?? null;
    }

    /**
     * Build the "start a new application" URL for the given form type.
     * Returns null when the workspace doesn't claim the form type or has
     * no start route configured (e.g. workspace-native domains like Liens).
     */
    public function startRouteFor(string $formType): ?string
    {
        if (! $this->claimsFormType($formType) || ! $this->startRouteName) {
            return null;
        }

        $params = $this->startRouteParam
            ? [$this->startRouteParam => $formType]
            : [];

        return route($this->startRouteName, $params);
    }

    /**
     * Build the form-runner detail URL for the given application.
     * Refuses to generate a URL when the workspace does not claim the
     * application's form_type, so cross-workspace bugs fail fast.
     *
     * @param  array<string, scalar>  $extraParams
     */
    public function applicationRouteFor(FormApplication $app, array $extraParams = []): ?string
    {
        if (! $this->claimsFormType($app->form_type) || ! $this->applicationRouteName) {
            return null;
        }

        return route($this->applicationRouteName, ['application' => $app, ...$extraParams]);
    }
}
