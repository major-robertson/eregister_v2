<?php

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
        'form_type' => null,
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
        'form_type' => 'sales_tax_permit',
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

];
