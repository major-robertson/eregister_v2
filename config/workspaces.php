<?php

use App\Domains\Forms\Models\LlcFormation;
use App\Domains\Forms\Models\SalesTaxRegistration;
use App\Support\Workspaces\FormationsWorkspaceData;
use App\Support\Workspaces\LienWorkspaceData;
use App\Support\Workspaces\SalesTaxWorkspaceData;

/*
|--------------------------------------------------------------------------
| Workspace Registry
|--------------------------------------------------------------------------
|
| Each entry describes a workspace (sub-portal) the user can enter from
| /portal. The array key is the internal workspace key used by
| `<x-layouts.workspace key="..."/>` and `WorkspaceRegistry::find($key)`.
| The `slug` is the URL slug (independent from the array key).
|
| All values must be config-cache-safe scalars or class strings — no
| closures — so `php artisan config:cache` works in production.
|
| `form_types` lists the forms-runner form types this workspace claims.
| When non-empty, the workspace's `start_route_name` and
| `application_route_name` host the form runner UI (state selector +
| MultiStateFormRunner). When empty (e.g. Liens), the workspace owns its
| own non-form routes and models.
|
*/

return [

    'liens' => [
        'name' => 'Liens',
        'slug' => 'liens',
        'description' => 'Track deadlines and file mechanics liens across your projects.',
        'icon' => 'scale',
        'badge' => 'Liens',
        'badge_color' => 'amber',
        'bg_class' => 'bg-amber-50/30',
        'dashboard_route' => 'lien.dashboard',
        'enabled' => true,
        'form_types' => [],
        'start_route_name' => null,
        'application_route_name' => null,
        'start_route_param' => null,
        'data_resolver' => LienWorkspaceData::class,
        'nav' => [
            [
                'label' => 'Dashboard',
                'icon' => 'home',
                'route' => 'lien.dashboard',
                'current_pattern' => 'lien.dashboard',
            ],
            [
                'label' => 'Projects',
                'icon' => 'folder',
                'route' => 'lien.projects.index',
                'current_pattern' => 'lien.projects.*',
            ],
            [
                'label' => 'Filings',
                'icon' => 'document-text',
                'route' => 'lien.filings.index',
                'current_pattern' => 'lien.filings.index',
            ],
            [
                'label' => 'Deadlines',
                'icon' => 'calendar',
                'route' => 'lien.deadlines.index',
                'current_pattern' => 'lien.deadlines.*',
            ],
        ],
        'nav_heading' => 'Lien Management',
    ],

    'sales_tax' => [
        'name' => 'Sales Tax',
        'slug' => 'sales-tax',
        'description' => 'Register for sales and use tax permits across one or more states.',
        'icon' => 'receipt-percent',
        'badge' => 'Sales Tax',
        'badge_color' => 'emerald',
        'bg_class' => 'bg-emerald-50/30',
        'dashboard_route' => 'sales-tax.dashboard',
        'enabled' => true,
        // Sourced from the child model so the SalesTaxRegistration global
        // scope, the workspace registry, and the data resolver share one
        // source of truth.
        'form_types' => [SalesTaxRegistration::FORM_TYPE],
        'start_route_name' => 'sales-tax.registrations.start',
        'application_route_name' => 'sales-tax.registrations.show',
        'start_route_param' => null, // form_type baked in via Route::defaults()
        'data_resolver' => SalesTaxWorkspaceData::class,
        'nav' => [
            [
                'label' => 'Dashboard',
                'icon' => 'home',
                'route' => 'sales-tax.dashboard',
                'current_pattern' => 'sales-tax.dashboard',
            ],
        ],
        'nav_heading' => 'Sales Tax',
    ],

    'formations' => [
        'name' => 'Formations',
        'slug' => 'formations',
        'description' => 'Form your business and manage your formation documents.',
        'icon' => 'building-office-2',
        'badge' => 'Formations',
        'badge_color' => 'indigo',
        'bg_class' => 'bg-indigo-50/30',
        'dashboard_route' => 'formations.dashboard',
        'enabled' => true,
        // Add more form types here (corporation, dba, nonprofit, sole_proprietorship)
        // and the start route's whereIn() constraint picks them up automatically.
        // Sourced from the child models so each model's global scope and the
        // workspace registry stay aligned without string drift.
        'form_types' => [LlcFormation::FORM_TYPE],
        'start_route_name' => 'formations.start',
        'application_route_name' => 'formations.show',
        'start_route_param' => 'formType', // {formType} URL segment, hydrates StateSelector::mount(string $formType)
        'data_resolver' => FormationsWorkspaceData::class,
        'nav' => [
            [
                'label' => 'Dashboard',
                'icon' => 'home',
                'route' => 'formations.dashboard',
                'current_pattern' => 'formations.dashboard',
            ],
        ],
        'nav_heading' => 'Formations',
    ],

];
